<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Model\ReferenceRecord;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Reference\InMemoryReferenceIndex;
use Payum\Core\Reference\MerchantReference;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Reference\StorageReferenceIndex;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

final class PayumWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        WebhookNotifyHandler::reset();
    }

    public function testShouldAnswerAVerifiedWebhookWith204(): void
    {
        $response = $this->buildPayum()->webhook('acme');

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        $this->assertTrue(WebhookNotifyHandler::$handled);
    }

    public function testShouldAnswerAFailedVerificationWith400(): void
    {
        WebhookNotifyHandler::$forged = true;

        $response = $this->buildPayum()->webhook('acme');

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
        $this->assertFalse(WebhookNotifyHandler::$handled);
    }

    public function testShouldAnswerRetryWith503(): void
    {
        WebhookNotifyHandler::$type = 'payment.captured';
        WebhookNotifyHandler::$merchantReference = 'py_unknown';

        $response = $this->buildPayum()->webhook('acme');

        $this->assertSame(503, $response->getStatusCode());
    }

    public function testShouldUseTheHandlersAcknowledgement(): void
    {
        WebhookNotifyHandler::$acknowledgement = Acknowledgement::ok('[accepted]');

        $response = $this->buildPayum()->webhook('acme');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[accepted]', $response->getContent());
    }

    public function testShouldVerifyTheRequestItIsGiven(): void
    {
        $response = $this->buildPayum()->webhook('acme', Request::create(
            '/webhooks/acme',
            'POST',
            server: [
                'HTTP_ACME_SIGNATURE' => 'sig_1',
            ],
            content: '{"id":"evt_given"}',
        ));

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('{"id":"evt_given"}', WebhookNotifyHandler::$body);
        $this->assertSame('sig_1', WebhookNotifyHandler::$signature);
        $this->assertSame(1, WebhookNotifyHandler::$verifications);
    }

    public function testShouldFindThePaymentFromTheMerchantReferenceTheGatewaySent(): void
    {
        $payum = $this->buildPayum();
        $payment = $this->savedPayment($payum);

        $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));
        WebhookNotifyHandler::$merchantReference = WebhookCaptureHandler::$sent;
        WebhookNotifyHandler::$type = 'payment.captured';

        $response = $payum->webhook('acme');

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame($payment->getNumber(), WebhookNotifyHandler::$seen?->getNumber());
        $this->assertSame(PaymentStatus::Captured, $payum->getStorage(Payment::class)->find($payment->getNumber())?->getStatus());
    }

    public function testShouldRunOnTheGatewayThatOwnsTheReference(): void
    {
        $payum = $this->buildPayum(names: ['acme_us', 'acme_eu']);
        $payment = $this->savedPayment($payum);

        $payum->getGateway('acme_us')->execute(CaptureCommand::forPayment($payment));
        WebhookNotifyHandler::$merchantReference = WebhookCaptureHandler::$sent;
        WebhookNotifyHandler::$type = 'payment.captured';

        $response = $payum->webhook('acme_eu');

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('acme_us', WebhookNotifyHandler::$gatewayName);
        $this->assertSame($payment->getNumber(), WebhookNotifyHandler::$seen?->getNumber());
        $this->assertSame(1, WebhookNotifyHandler::$verifications);
    }

    public function testShouldStayOnTheReceivingGatewayWhenTheOwnerIsAnotherPsp(): void
    {
        $payum = $this->buildPayum(others: ['other_psp']);
        $payment = $this->savedPayment($payum);

        // Retried on another PSP, which recorded the merchant reference last.
        $payum->getGateway('other_psp')->execute(CaptureCommand::forPayment($payment));
        WebhookNotifyHandler::$merchantReference = WebhookCaptureHandler::$sent;
        WebhookNotifyHandler::$type = 'payment.captured';

        $response = $payum->webhook('acme');

        $this->assertSame(204, $response->getStatusCode());
        $this->assertSame('acme', WebhookNotifyHandler::$gatewayName);
        $this->assertSame($payment->getNumber(), WebhookNotifyHandler::$seen?->getNumber());
    }

    public function testShouldStayOnTheReceivingGatewayWhenTheOwnerIsNoLongerRegistered(): void
    {
        $index = new InMemoryReferenceIndex();
        $payum = $this->buildPayum(index: $index);
        $payment = $this->savedPayment($payum);
        $identity = new Identity($payment->getNumber(), Payment::class);
        $index->record(MerchantReference::NAMESPACE, MerchantReference::of($identity), 'acme_removed', $identity);
        WebhookNotifyHandler::$merchantReference = MerchantReference::of($identity);

        $payum->webhook('acme');

        $this->assertSame('acme', WebhookNotifyHandler::$gatewayName);
        $this->assertSame($payment->getNumber(), WebhookNotifyHandler::$seen?->getNumber());
    }

    public function testShouldKeepReferencesInAStorage(): void
    {
        $dir = sys_get_temp_dir() . '/payum-references-' . uniqid();
        mkdir($dir);

        try {
            $payum = $this->buildPayum(index: new StorageReferenceIndex(new FilesystemStorage($dir, ReferenceRecord::class, 'key')));
            $payment = $this->savedPayment($payum);

            $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));
            WebhookNotifyHandler::$merchantReference = WebhookCaptureHandler::$sent;

            $payum->webhook('acme');

            $this->assertSame($payment->getNumber(), WebhookNotifyHandler::$seen?->getNumber());
        } finally {
            array_map(unlink(...), glob($dir . '/*') ?: []);
            rmdir($dir);
        }
    }

    /**
     * @param Payum<object> $payum
     */
    private function savedPayment(Payum $payum): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid('', true));
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setStatus(PaymentStatus::Pending);
        $payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }

    /**
     * @param list<string> $names
     * @param list<string> $others gateway names on another PSP
     *
     * @return Payum<object>
     */
    private function buildPayum(array $names = ['acme'], ?ReferenceIndexInterface $index = null, array $others = []): Payum
    {
        $builder = (new PayumBuilder())
            ->addDefaultStorages()
            ->setReferenceIndex($index ?? new InMemoryReferenceIndex())
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://payum.dev/webhooks/acme'));

        foreach ($names as $name) {
            $builder->registerGateway($name, new WebhookConfig());
        }

        foreach ($others as $name) {
            $builder->registerGateway($name, new OtherPspConfig());
        }

        return $builder->getPayum();
    }
}

final class WebhookConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return WebhookGateway::class;
    }
}

final class WebhookGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return WebhookConfig::class;
    }

    public function handlers(): array
    {
        return [WebhookNotifyHandler::class, WebhookCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Webhook';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class OtherPspConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return OtherPspGateway::class;
    }
}

final class OtherPspGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return OtherPspConfig::class;
    }

    public function handlers(): array
    {
        return [WebhookCaptureHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://other.test/logo.svg');
    }

    public function name(): string
    {
        return 'Other PSP';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://other.test');
    }
}

final class WebhookNotifyHandler implements NotifyHandlerInterface
{
    public static bool $forged = false;

    public static ?string $type = null;

    public static ?string $merchantReference = null;

    public static ?Acknowledgement $acknowledgement = null;

    public static bool $handled = false;

    public static int $verifications = 0;

    public static ?Payment $seen = null;

    public static ?string $gatewayName = null;

    public static ?string $body = null;

    public static ?string $signature = null;

    public static function reset(): void
    {
        self::$forged = false;
        self::$type = null;
        self::$merchantReference = null;
        self::$acknowledgement = null;
        self::$handled = false;
        self::$verifications = 0;
        self::$seen = null;
        self::$gatewayName = null;
        self::$body = null;
        self::$signature = null;
        WebhookCaptureHandler::$sent = null;
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        ++self::$verifications;
        self::$body = (string) $request->getBody();
        self::$signature = $request->getHeaderLine('Acme-Signature');

        if (self::$forged) {
            throw new WebhookNotVerifiedException('The signature does not match.');
        }

        return WebhookEvent::verified([], 'evt_1', self::$type, merchantReference: self::$merchantReference);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        self::$handled = true;
        self::$gatewayName = $context->gatewayName();
        $payment = $context->payment();
        self::$seen = $payment instanceof Payment ? $payment : null;

        if ('payment.captured' !== $event->type) {
            return NotifyResult::ignored(self::$acknowledgement);
        }

        if (null === $payment) {
            return NotifyResult::retry();
        }

        return NotifyResult::handled(PaymentStatus::Captured);
    }
}

final class WebhookCaptureHandler implements CaptureHandlerInterface
{
    public static ?string $sent = null;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        self::$sent = $context->merchantReference();

        return CaptureResult::accepted('pi_1');
    }
}
