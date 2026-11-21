<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use DateTimeImmutable;
use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\SyncSubscriptionCommand;
use Payum\Core\Event\SubscriptionPaymentFailed;
use Payum\Core\Event\SubscriptionPaymentSucceeded;
use Payum\Core\Event\SubscriptionStatusChanged;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\Customer;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Core\Security\TokenPurpose;
use Payum\Offline\OfflineConfig;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Runs the samples of docs/subscriptions.md against the Offline gateway.
 */
final class SubscriptionDocsTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $server;

    /**
     * @var Payum<object>
     */
    private Payum $payum;

    private EventDispatcher $dispatcher;

    private DocsSubscriptionMailer $mailer;

    protected function setUp(): void
    {
        $this->server = $_SERVER;
        $_SERVER['HTTP_HOST'] = 'shop.example.com';
        $_SERVER['SCRIPT_NAME'] = '/prepare.php';

        $this->mailer = new DocsSubscriptionMailer();
        $this->payum = $this->buildPayum(new OfflineConfig());
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
    }

    public function testShouldChooseTheGatewayScheduleWhenTheGatewayCreatesSubscriptions(): void
    {
        $gateway = $this->payum->getGateway('offline');
        $this->assertInstanceOf(Gateway::class, $gateway);
        $capabilities = $gateway->capabilities();

        $schedule = in_array(Capability::CreateSubscription, $capabilities, true)
            ? SubscriptionSchedule::Gateway
            : SubscriptionSchedule::Application;

        $this->assertSame(SubscriptionSchedule::Gateway, $schedule);
    }

    public function testShouldCreateAGatewayScheduledSubscriptionThroughAToken(): void
    {
        $payum = $this->payum;

        $subscription = new Subscription();
        $subscription->setGatewayName('offline');
        $subscription->setSchedule(SubscriptionSchedule::Gateway);
        $subscription->setCustomer($payum->getStorage(Customer::class)->find('customer-42'));
        $subscription->setTotalAmount(1000);
        $subscription->setCurrencyCode('EUR');
        $subscription->setInterval(BillingInterval::fromString('1 month'));
        $subscription->setDescription('Pro plan');
        $payum->getStorage(Subscription::class)->update($subscription);

        $token = $payum->getTokenIssuer()->create(TokenPurpose::CreateSubscription, 'offline', $subscription, 'subscribed.php');
        $this->assertStringContainsString('subscribe.php', $token->getTargetUrl());

        $response = $payum->subscribe(Request::create($token->getTargetUrl()));
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringContainsString('subscribed.php', $response->getTargetUrl());

        $token = $payum->getHttpRequestVerifier()->verify(Request::create($response->getTargetUrl()));
        $payum->getHttpRequestVerifier()->invalidate($token);

        $identity = $token->getDetails();
        $subscription = $payum->getStorage($identity->getClass())->find($identity);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertNotNull($subscription->getReference());
    }

    public function testShouldCreateAGatewayScheduledSubscriptionFromCode(): void
    {
        $subscription = $this->subscription(SubscriptionSchedule::Gateway);

        $this->payum->getGateway('offline')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
    }

    public function testShouldCancelNowAndAtThePeriodEnd(): void
    {
        $subscription = $this->activeSubscription();
        $subscription->setCurrentPeriodEnd(new DateTimeImmutable('2026-12-21'));
        $gateway = $this->payum->getGateway($subscription->getGatewayName());

        $gateway->execute(CancelSubscriptionCommand::forSubscription($subscription, atPeriodEnd: true));

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertEquals(new DateTimeImmutable('2026-12-21'), $subscription->getCancelAt());

        $gateway->execute(CancelSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(SubscriptionStatus::Canceled, $subscription->getStatus());
        $this->assertNotNull($subscription->getCanceledAt());
    }

    public function testShouldSyncTheSubscription(): void
    {
        $subscription = $this->activeSubscription();

        $this->payum->getGateway($subscription->getGatewayName())->execute(SyncSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
    }

    public function testShouldChargeAnApplicationScheduledSubscription(): void
    {
        $this->listen();
        $payum = $this->payum;

        $customer = $payum->getStorage(Customer::class)->find('customer-42');

        $method = new StoredPaymentMethod();
        $method->setGatewayName('offline');
        $method->setCustomer($customer);
        $payum->getStorage(StoredPaymentMethod::class)->update($method);

        $subscription = new Subscription();
        $subscription->setGatewayName('offline');
        $subscription->setSchedule(SubscriptionSchedule::Application);
        $subscription->setCustomer($customer);
        $subscription->setStoredPaymentMethod($method);
        $subscription->setTotalAmount(1000);
        $subscription->setCurrencyCode('EUR');
        $subscription->setInterval(BillingInterval::fromString('1 month'));
        $payum->getStorage(Subscription::class)->update($subscription);

        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setCustomer($customer);
        $payment->setStoredPaymentMethod($method);
        $payment->setSubscription($subscription);

        $captureToken = $payum->prepare('offline', $payment);
        $payum->capture(Request::create($captureToken->getTargetUrl()));

        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertSame(['customer-42'], $this->mailer->welcomed);
        $firstPeriodEnd = $subscription->getCurrentPeriodEnd();
        $this->assertNotNull($firstPeriodEnd);

        $subscriptionId = $payum->getStorage(Subscription::class)->identify($subscription)->getId();
        $result = $this->renew($subscriptionId);

        $this->assertInstanceOf(CaptureResult::class, $result);
        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertSame(['customer-42'], $this->mailer->welcomed);
        $this->assertEquals($firstPeriodEnd->modify('+1 month'), $subscription->getCurrentPeriodEnd());
    }

    public function testShouldNotLetTheGatewayCreateAnApplicationScheduledSubscription(): void
    {
        $this->expectException(LogicException::class);

        $this->payum->getGateway('offline')->execute(CreateSubscriptionCommand::forSubscription($this->subscription(SubscriptionSchedule::Application)));
    }

    public function testAFailedRenewalShouldMarkTheSubscriptionPastDue(): void
    {
        $this->payum = $this->buildPayum(new OfflineConfig(handlers: [new DocsDecliningCaptureHandler(FailureReason::InsufficientFunds)]));
        $this->listen();
        $subscription = $this->subscription(SubscriptionSchedule::Application);
        $subscription->getStoredPaymentMethod()?->apply(StoredPaymentMethodDetails::active('offline_existing'));

        $this->renew($this->payum->getStorage(Subscription::class)->identify($subscription)->getId());

        $this->assertSame(SubscriptionStatus::PastDue, $subscription->getStatus());
        $this->assertSame([FailureReason::InsufficientFunds], $this->mailer->failed);
    }

    public function testShouldAskTheCustomerBackWhenTheBankWantsThem(): void
    {
        $payum = $this->payum = $this->buildPayum(new OfflineConfig(handlers: [new DocsDecliningCaptureHandler(FailureReason::AuthenticationRequired)]));
        $mailer = $this->mailer;
        $subscription = $this->subscription(SubscriptionSchedule::Application);
        $subscription->getStoredPaymentMethod()?->apply(StoredPaymentMethodDetails::active('offline_existing'));
        $payment = $this->payment($subscription);

        $result = $payum->getGateway($subscription->getGatewayName())->execute(CaptureCommand::forPayment($payment));

        if (FailureReason::AuthenticationRequired === $result->failure?->reason) {
            $token = $payum->prepare($subscription->getGatewayName(), $payment, 'done.php');

            $mailer->askToConfirmPayment($subscription->getCustomer(), $token->getTargetUrl());
        }

        $this->assertCount(1, $mailer->confirmUrls);
        $this->assertStringContainsString('capture.php', $mailer->confirmUrls[0]);
    }

    public function testAStatusChangeShouldRevokeAccess(): void
    {
        $access = new class() {
            /**
             * @var list<string>
             */
            public array $revoked = [];

            public function revoke(?CustomerInterface $customer): void
            {
                $this->revoked[] = (string) $customer?->getCustomerId();
            }
        };
        $dispatcher = $this->dispatcher;

        $dispatcher->addListener(SubscriptionStatusChanged::class, function (SubscriptionStatusChanged $event) use ($access): void {
            if (SubscriptionStatus::Canceled === $event->to || SubscriptionStatus::Expired === $event->to) {
                $access->revoke($event->subscription->getCustomer());
            }
        });

        $subscription = $this->activeSubscription();
        $this->assertSame([], $access->revoked);

        $this->payum->getGateway('offline')->execute(CancelSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(['customer-42'], $access->revoked);
    }

    /**
     * The cron sample, renew.php.
     */
    private function renew(mixed $subscriptionId): ?CaptureResult
    {
        $payum = $this->payum;

        $subscription = $payum->getStorage(Subscription::class)->find($subscriptionId);
        $this->assertInstanceOf(Subscription::class, $subscription);
        $method = $subscription->getStoredPaymentMethod();

        if (StoredPaymentMethodStatus::Active !== $method?->getStatus()) {
            return null;
        }

        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode($subscription->getCurrencyCode());
        $payment->setTotalAmount($subscription->getTotalAmount());
        $payment->setCustomer($subscription->getCustomer());
        $payment->setStoredPaymentMethod($method);
        $payment->setSubscription($subscription);
        $payum->getStorage(Payment::class)->update($payment);

        $result = $payum->getGateway($subscription->getGatewayName())->execute(CaptureCommand::forPayment($payment));

        $payum->getStorage(Payment::class)->update($payment);
        $this->assertInstanceOf(CaptureResult::class, $result);

        return $result;
    }

    /**
     * The listeners of "Know about each charge".
     */
    private function listen(): void
    {
        $dispatcher = $this->dispatcher;
        $payum = $this->payum;
        $mailer = $this->mailer;

        $dispatcher->addListener(SubscriptionPaymentSucceeded::class, function (SubscriptionPaymentSucceeded $event) use ($payum, $mailer): void {
            $subscription = $event->subscription;

            if ($subscription instanceof Subscription && SubscriptionSchedule::Application === $subscription->getSchedule()) {
                $periodStart = $subscription->getCurrentPeriodEnd() ?? new DateTimeImmutable();
                $subscription->setStatus(SubscriptionStatus::Active);
                $subscription->setCurrentPeriodEnd($periodStart->modify('+' . $subscription->getInterval()?->toString()));
                $payum->getStorage(Subscription::class)->update($subscription);
            }

            if ($event->isFirst) {
                $mailer->sendWelcome($subscription->getCustomer());
            }
        });

        $dispatcher->addListener(SubscriptionPaymentFailed::class, function (SubscriptionPaymentFailed $event) use ($payum, $mailer): void {
            $subscription = $event->subscription;

            if ($subscription instanceof Subscription && SubscriptionSchedule::Application === $subscription->getSchedule()) {
                $subscription->setStatus(SubscriptionStatus::PastDue);
                $payum->getStorage(Subscription::class)->update($subscription);
            }

            $mailer->sendPaymentFailed($subscription->getCustomer(), $event->failure?->reason);
        });
    }

    /**
     * @return Payum<object>
     */
    private function buildPayum(OfflineConfig $config): Payum
    {
        $this->dispatcher = new EventDispatcher();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(EventDispatcherInterface::class, $this->dispatcher)
            ->registerGateway('offline', $config)
            ->getPayum();

        $customer = new Customer();
        $customer->setCustomerId('customer-42');
        $payum->getStorage(Customer::class)->update($customer);

        return $payum;
    }

    private function subscription(SubscriptionSchedule $schedule): Subscription
    {
        $customer = $this->payum->getStorage(Customer::class)->find('customer-42');
        $this->assertInstanceOf(Customer::class, $customer);

        $method = new StoredPaymentMethod();
        $method->setGatewayName('offline');
        $method->setCustomer($customer);
        $this->payum->getStorage(StoredPaymentMethod::class)->update($method);

        $subscription = new Subscription();
        $subscription->setGatewayName('offline');
        $subscription->setSchedule($schedule);
        $subscription->setCustomer($customer);
        $subscription->setStoredPaymentMethod($method);
        $subscription->setTotalAmount(1000);
        $subscription->setCurrencyCode('EUR');
        $subscription->setInterval(BillingInterval::fromString('1 month'));
        $this->payum->getStorage(Subscription::class)->update($subscription);

        return $subscription;
    }

    private function activeSubscription(): Subscription
    {
        $subscription = $this->subscription(SubscriptionSchedule::Gateway);
        $this->payum->getGateway('offline')->execute(CreateSubscriptionCommand::forSubscription($subscription));

        return $subscription;
    }

    private function payment(Subscription $subscription): Payment
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setCustomer($subscription->getCustomer());
        $payment->setStoredPaymentMethod($subscription->getStoredPaymentMethod());
        $payment->setSubscription($subscription);
        $this->payum->getStorage(Payment::class)->update($payment);

        return $payment;
    }
}

final class DocsSubscriptionMailer
{
    /**
     * @var list<string>
     */
    public array $welcomed = [];

    /**
     * @var list<FailureReason|null>
     */
    public array $failed = [];

    /**
     * @var list<string>
     */
    public array $confirmUrls = [];

    public function sendWelcome(?CustomerInterface $customer): void
    {
        $this->welcomed[] = (string) $customer?->getCustomerId();
    }

    public function sendPaymentFailed(?CustomerInterface $customer, ?FailureReason $reason): void
    {
        $this->failed[] = $reason;
    }

    public function askToConfirmPayment(?CustomerInterface $customer, string $url): void
    {
        $this->confirmUrls[] = $url;
    }
}

final class DocsDecliningCaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly FailureReason $reason,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return CaptureResult::failed(new Failure($this->reason), PaymentStatus::Failed);
    }
}
