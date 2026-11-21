<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Event;

use Http\Discovery\Psr17FactoryDiscovery;
use League\Uri\Uri;
use Money\Money;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Event\SubscriptionPaymentFailed;
use Payum\Core\Event\SubscriptionPaymentSucceeded;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\CreateSubscriptionHandlerInterface;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Metadata\Logo;
use Payum\Core\Metadata\Logo\Url;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Model\Token;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenPurpose;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Tests\Mocks\Event\RecordingEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

final class SubscriptionPaymentEventsTest extends TestCase
{
    private string $dir;

    private RecordingEventDispatcher $events;

    /**
     * @var Payum<object>
     */
    private Payum $payum;

    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];

        $this->dir = sys_get_temp_dir() . '/payum-subscription-events-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
        $this->events = new RecordingEventDispatcher();
        AcmeChargesCaptureHandler::$result = CaptureResult::captured('pi_1');
        AcmeChargesCreateHandler::$renewal = null;

        /** @var FilesystemStorage<TokenInterface> $tokens */
        $tokens = new FilesystemStorage($this->dir, Token::class, 'hash');
        $this->payum = (new PayumBuilder())
            ->addStorage(Payment::class, new FilesystemStorage($this->dir, Payment::class))
            ->addStorage(Subscription::class, new FilesystemStorage($this->dir, Subscription::class))
            ->addStorage(StoredPaymentMethod::class, new FilesystemStorage($this->dir, StoredPaymentMethod::class))
            ->setTokenStorage($tokens)
            ->addGlobalService(EventDispatcherInterface::class, $this->events)
            ->addGlobalService(ServerRequestInterface::class, Psr17FactoryDiscovery::findServerRequestFactory()
                ->createServerRequest('POST', 'https://payum.dev/webhooks/acme'))
            ->registerGateway('acme', new AcmeChargesConfig())
            ->getPayum();
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->dir . '/*') ?: []);
        rmdir($this->dir);
    }

    public function testAnApplicationScheduledChargeAnnouncesSuccess(): void
    {
        $subscription = $this->applicationSubscription();
        $method = new StoredPaymentMethod();
        $method->apply(StoredPaymentMethodDetails::active('pm_1'));
        $payment = $this->paymentOf($subscription, $method);

        $this->payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $events = $this->events->ofType(SubscriptionPaymentSucceeded::class);
        $this->assertCount(1, $events);
        $this->assertSame($subscription, $events[0]->subscription);
        $this->assertSame($payment, $events[0]->payment);
        $this->assertFalse($events[0]->isFirst);
        $this->assertSame([], $this->events->ofType(SubscriptionPaymentFailed::class));
    }

    public function testTheFirstChargeIsMarkedFirst(): void
    {
        $payment = $this->paymentOf($this->applicationSubscription(), new StoredPaymentMethod());
        $token = $this->payum->getTokenIssuer()->create(TokenPurpose::Capture, 'acme', $payment, 'https://payum.dev/done');
        AcmeChargesCaptureHandler::$result = CaptureResult::captured('pi_1')->withStoredPaymentMethod(StoredPaymentMethodDetails::active('pm_1'));

        $this->payum->getGateway('acme')->execute(CaptureCommand::forToken($token));

        $events = $this->events->ofType(SubscriptionPaymentSucceeded::class);
        $this->assertCount(1, $events);
        $this->assertTrue($events[0]->isFirst);
        $this->assertInstanceOf(Payment::class, $events[0]->payment);
        $this->assertSame('pm_1', $events[0]->payment->getStoredPaymentMethod()?->getReference());
    }

    public function testAFailedChargeAnnouncesFailureWithItsReason(): void
    {
        $method = new StoredPaymentMethod();
        $method->apply(StoredPaymentMethodDetails::active('pm_1'));
        $payment = $this->paymentOf($this->applicationSubscription(), $method);
        $failure = new Failure(FailureReason::AuthenticationRequired);
        AcmeChargesCaptureHandler::$result = CaptureResult::failed($failure, PaymentStatus::Failed);

        $this->payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $events = $this->events->ofType(SubscriptionPaymentFailed::class);
        $this->assertCount(1, $events);
        $this->assertSame($failure, $events[0]->failure);
        $this->assertFalse($events[0]->isFirst);
        $this->assertSame([], $this->events->ofType(SubscriptionPaymentSucceeded::class));
    }

    public function testAGatewayRenewalAnnouncesFailureWithItsReason(): void
    {
        $subscription = $this->gatewaySubscription();
        $failure = new Failure(FailureReason::InsufficientFunds, 'card_declined');
        AcmeChargesCreateHandler::$renewal = new Renewal('in_2', Money::EUR(1500), PaymentStatus::Failed, failure: $failure);

        $this->payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));

        $events = $this->events->ofType(SubscriptionPaymentFailed::class);
        $this->assertCount(1, $events);
        $this->assertSame($failure, $events[0]->failure);
        $this->assertFalse($events[0]->isFirst);
        $this->assertSame($subscription->getReference(), $events[0]->subscription->getReference());
        $this->assertSame('in_2', $events[0]->payment->getNumber());
        $this->assertInstanceOf(NotifyCommand::class, $events[0]->command);
    }

    public function testTheChargeThatCreatesTheSubscriptionIsMarkedFirst(): void
    {
        AcmeChargesCreateHandler::$renewal = new Renewal('in_1', Money::EUR(1500), PaymentStatus::Captured);

        $this->gatewaySubscription();

        $events = $this->events->ofType(SubscriptionPaymentSucceeded::class);
        $this->assertCount(1, $events);
        $this->assertTrue($events[0]->isFirst);
    }

    public function testAPaymentWithoutASubscriptionAnnouncesNothing(): void
    {
        $payment = new Payment();
        $this->payum->getStorage(Payment::class)->update($payment);

        $this->payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

        $this->assertCount(1, $this->events->ofType(StatusChanged::class));
        $this->assertSame([], $this->events->ofType(SubscriptionPaymentSucceeded::class));
        $this->assertSame([], $this->events->ofType(SubscriptionPaymentFailed::class));
    }

    public function testNoSecondEventForARedelivery(): void
    {
        $this->gatewaySubscription();
        AcmeChargesCreateHandler::$renewal = new Renewal('in_2', Money::EUR(1500), PaymentStatus::Captured);

        $this->payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));
        $this->payum->webhook('acme', Request::create('https://payum.dev/webhooks/acme', 'POST'));

        $events = $this->events->ofType(SubscriptionPaymentSucceeded::class);
        $this->assertCount(1, $events);
        $this->assertFalse($events[0]->isFirst);
    }

    private function applicationSubscription(): Subscription
    {
        $subscription = new Subscription();
        $subscription->setSchedule(SubscriptionSchedule::Application);
        $this->payum->getStorage(Subscription::class)->update($subscription);

        return $subscription;
    }

    /**
     * Subscription sub_1, created on the gateway; the events of creating it are left in place.
     */
    private function gatewaySubscription(): Subscription
    {
        $subscription = new Subscription();
        $this->payum->getStorage(Subscription::class)->update($subscription);
        $this->payum->getGateway('acme')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        return $subscription;
    }

    private function paymentOf(Subscription $subscription, StoredPaymentMethod $method): Payment
    {
        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);
        $payment = new Payment();
        $payment->setSubscription($subscription);
        $payment->setStoredPaymentMethod($method);
        $this->payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }
}

final class AcmeChargesConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AcmeChargesGateway::class;
    }
}

final class AcmeChargesGateway implements PaymentGateway
{
    public function configClass(): string
    {
        return AcmeChargesConfig::class;
    }

    public function handlers(): array
    {
        return [AcmeChargesCaptureHandler::class, AcmeChargesCreateHandler::class, AcmeChargesNotifyHandler::class];
    }

    public function logo(): Logo
    {
        return Url::create('https://acme.test/logo.svg');
    }

    public function name(): string
    {
        return 'Acme Charges';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }
}

final class AcmeChargesCaptureHandler implements CaptureHandlerInterface
{
    public static CaptureResult $result;

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return self::$result;
    }
}

final class AcmeChargesCreateHandler implements CreateSubscriptionHandlerInterface
{
    public static ?Renewal $renewal = null;

    public function handle(CreateSubscriptionCommand $command, Context $context): SubscriptionResult
    {
        $result = SubscriptionResult::active(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1'));

        return self::$renewal instanceof Renewal ? $result->withRenewal(self::$renewal) : $result;
    }
}

final class AcmeChargesNotifyHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::handled()->withRenewal(AcmeChargesCreateHandler::$renewal ?? throw new LogicException('No renewal to report.'));
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::verified([], 'evt_1', 'invoice.paid', null, 'sub_1');
    }
}
