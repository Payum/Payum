<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\SyncHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * End to end: a PSP posts to the notify endpoint, the gateway checks the message is genuine, and what
 * it says is recorded against the payment.
 */
final class NotifyTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        AcmeNotifyHandler::$signature = 'good-signature';
        VerificationRecordingMiddleware::$seen = null;
    }

    public function testShouldHandleAVerifiedEvent(): void
    {
        $payment = $this->buildPayment();
        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertInstanceOf(NotifyResult::class, $result);
        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame('txn_1', $result->transactionId);
    }

    public function testShouldRecordWhatTheEventSaidAboutThePayment(): void
    {
        $payment = new TrackedNotifyPayment();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    public function testShouldRefuseAMessageThatIsNotGenuine(): void
    {
        $payum = $this->buildPayum($this->buildRequest('forged'));

        $this->expectException(WebhookNotVerifiedException::class);

        $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment()));
    }

    public function testShouldLeaveTheStatusAloneWhenTheMessageIsNotGenuine(): void
    {
        $payment = new TrackedNotifyPayment();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('forged'));

        try {
            $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));
        } catch (WebhookNotVerifiedException) {
            // The point of the test is what did not happen.
        }

        $this->assertSame(PaymentStatus::Pending, $payment->getStatus());
    }

    public function testShouldLeaveThePaymentAloneForAnEventItIgnores(): void
    {
        $payment = new TrackedNotifyPayment();
        $payment->setNumber(uniqid());
        $payment->setStatus(PaymentStatus::Pending);

        $payum = $this->buildPayum($this->buildRequest('good-signature', 'invoice.drafted'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertNull($result->status);
        $this->assertSame(PaymentStatus::Pending, $payment->getStatus());
    }

    public function testShouldReadTheStateAPreviousCommandWrote(): void
    {
        $payment = $this->buildPayment();
        $payment->setDetails([
            'checkout_id' => 'chk_1',
        ]);

        $payum = $this->buildPayum($this->buildRequest('good-signature'));

        $result = $payum->getGateway('acme')->execute(NotifyCommand::forPayment($payment));

        $this->assertSame('chk_1', $result->raw['seen_checkout']);
    }

    public function testShouldLetAGatewayThatCannotVerifyReReadFromThePsp(): void
    {
        $payment = $this->buildPayment();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('irrelevant'))
            ->registerGateway('trusting', new TrustingNotifyConfig())
            ->getPayum();

        $result = $payum->getGateway('trusting')->execute(NotifyCommand::forPayment($payment));

        // The event was taken on trust, so the answer comes from asking the PSP rather than from the
        // message.
        $this->assertSame(PaymentStatus::Refunded, $result->status);
    }

    public function testShouldRunVerificationInsideThePipeline(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ServerRequestInterface::class, $this->buildRequest('forged'))
            ->addMiddleware(new VerificationRecordingMiddleware())
            ->registerGateway('acme', new AcmeNotifyConfig())
            ->getPayum();

        try {
            $payum->getGateway('acme')->execute(NotifyCommand::forPayment($this->buildPayment()));
        } catch (WebhookNotVerifiedException) {
            // The point of the test is what the middleware saw before this was rethrown.
        }

        $this->assertInstanceOf(WebhookNotVerifiedException::class, VerificationRecordingMiddleware::$seen);
    }

    public function testShouldAnswerThePspWithWhatTheHandlerAskedFor(): void
    {
        $payum = $this->buildPayum($this->buildRequest('good-signature'));
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify([
            'payum_token' => $token,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[accepted]', $response->getContent());
    }

    public function testShouldAnswerNoContentWhenTheHandlerNamesNothing(): void
    {
        $payum = $this->buildPayum($this->buildRequest('good-signature', 'invoice.drafted'));
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify([
            'payum_token' => $token,
        ]);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testShouldAnswerBadRequestWithoutSayingWhyWhenVerificationFails(): void
    {
        $payum = $this->buildPayum($this->buildRequest('forged'));
        $token = $this->mintNotifyToken($payum);

        $response = $payum->notify([
            'payum_token' => $token,
        ]);

        // Whoever failed the check is misconfigured or probing. Neither is helped by the detail.
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    /**
     * @param Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>> $payum
     */
    private function mintNotifyToken(Payum $payum): TokenInterface
    {
        $payment = $this->buildPayment();
        $payum->getStorage($payment::class)->update($payment);

        return $payum->getTokenFactory()->createNotifyToken('acme', $payment);
    }

    private function buildPayment(): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDescription('An order');

        return $payment;
    }

    private function buildRequest(string $signature, string $type = 'payment.captured'): ServerRequestInterface
    {
        $body = json_encode([
            'id' => 'evt_1',
            'type' => $type,
            'transaction' => 'txn_1',
        ], JSON_THROW_ON_ERROR);

        return Psr17FactoryDiscovery::findServerRequestFactory()
            ->createServerRequest('POST', 'https://payum.dev/notify.php')
            ->withHeader('Acme-Signature', $signature)
            ->withBody(Psr17FactoryDiscovery::findStreamFactory()->createStream($body));
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    private function buildPayum(ServerRequestInterface $request): Payum
    {
        return (new PayumBuilder())
            ->addDefaultStorages()
            // What a framework does: hand Payum the real request instead of the one built from globals.
            ->addGlobalService(ServerRequestInterface::class, $request)
            ->registerGateway('acme', new AcmeNotifyConfig())
            ->getPayum();
    }
}

final class AcmeNotifyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeNotifyGateway::class;
    }
}

final class AcmeNotifyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeNotifyConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeNotifyHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmeNotifyHandler implements NotifyHandlerInterface
{
    public static string $signature = 'good-signature';

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ('payment.captured' !== $event->type) {
            return NotifyResult::ignored();
        }

        return NotifyResult::handled(
            PaymentStatus::Captured,
            Acknowledgement::ok('[accepted]'),
            $event->payload['transaction'],
            raw: [
                'seen_checkout' => $context->state()['checkout_id'],
            ],
        );
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        if (! hash_equals(self::$signature, $request->getHeaderLine('Acme-Signature'))) {
            throw new WebhookNotVerifiedException('The signature does not match.');
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return WebhookEvent::verified($payload, $payload['id'], $payload['type']);
    }
}

final class TrustingNotifyConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return TrustingNotifyGateway::class;
    }
}

final class TrustingNotifyGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return TrustingNotifyConfig::class;
    }

    public function handlers(): array
    {
        return [TrustingNotifyHandler::class, TrustingSyncHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://trusting.test/logo.svg');
    }

    public function name(): string
    {
        return 'Trusting';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://trusting.test');
    }
}

final class TrustingNotifyHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        if ($event->isVerified()) {
            return NotifyResult::handled(PaymentStatus::Captured);
        }

        $payment = $context->payment();

        if (! $payment instanceof PaymentInterface) {
            return NotifyResult::ignored();
        }

        $synced = $context->execute(SyncCommand::forPayment($payment));

        return NotifyResult::handled($synced->status);
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::unverified([]);
    }
}

final class TrustingSyncHandler implements SyncHandlerInterface
{
    public function handle(SyncCommand $command, Context $context): SyncResult
    {
        return SyncResult::synced(PaymentStatus::Refunded, 'txn_2');
    }
}

final class VerificationRecordingMiddleware implements MiddlewareInterface
{
    public static ?WebhookNotVerifiedException $seen = null;

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        try {
            return $next($command, $context);
        } catch (WebhookNotVerifiedException $e) {
            self::$seen = $e;

            throw $e;
        }
    }
}

class TrackedNotifyPayment extends Payment implements StatusAwareInterface
{
    private PaymentStatus $status = PaymentStatus::New;

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
