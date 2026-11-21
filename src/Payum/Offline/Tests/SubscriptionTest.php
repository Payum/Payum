<?php

declare(strict_types=1);

namespace Payum\Offline\Tests;

use DateTimeImmutable;
use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\SyncSubscriptionCommand;
use Payum\Core\Event\SubscriptionPaymentSucceeded;
use Payum\Core\Gateway;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\StoredPaymentMethodStatus;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Result\SubscriptionStatus;
use Payum\Offline\OfflineConfig;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

final class SubscriptionTest extends TestCase
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

    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->server = $_SERVER;
        $_SERVER['HTTP_HOST'] = 'shop.example.com';
        $_SERVER['SCRIPT_NAME'] = '/prepare.php';

        $this->now = new DateTimeImmutable('2026-11-21 10:00:00');
        $now = $this->now;
        $this->dispatcher = new EventDispatcher();

        $this->payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(EventDispatcherInterface::class, $this->dispatcher)
            ->addGlobalService(ClockInterface::class, new class($now) implements ClockInterface {
                public function __construct(
                    private readonly DateTimeImmutable $now,
                ) {
                }

                public function now(): DateTimeImmutable
                {
                    return $this->now;
                }
            })
            ->registerGateway('offline', new OfflineConfig())
            ->getPayum();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
    }

    public function testShouldDeclareTheSubscriptionCapabilities(): void
    {
        $gateway = $this->payum->getGateway('offline');
        $this->assertInstanceOf(Gateway::class, $gateway);

        foreach ([Capability::CreateSubscription, Capability::CancelSubscription, Capability::SyncSubscription, Capability::CancelAtPeriodEnd] as $capability) {
            $this->assertContains($capability, $gateway->capabilities());
        }
    }

    public function testCreateActivates(): void
    {
        $subscription = $this->subscription(SubscriptionSchedule::Gateway);

        $result = $this->payum->getGateway('offline')->execute(CreateSubscriptionCommand::forSubscription($subscription));
        $this->assertInstanceOf(SubscriptionResult::class, $result);

        $this->assertSame(SubscriptionStatus::Active, $result->subscription()?->status);
        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertMatchesRegularExpression('/^offline_sub_[0-9a-f]{16}$/', (string) $subscription->getReference());
    }

    public function testCancelAtPeriodEndKeepsItActive(): void
    {
        $subscription = $this->activeSubscription();
        $periodEnd = new DateTimeImmutable('2026-12-21 10:00:00');
        $subscription->setCurrentPeriodEnd($periodEnd);

        $this->payum->getGateway('offline')->execute(CancelSubscriptionCommand::forSubscription($subscription, atPeriodEnd: true));

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertEquals($periodEnd, $subscription->getCancelAt());
        $this->assertNull($subscription->getCanceledAt());
    }

    public function testCancelNow(): void
    {
        $subscription = $this->activeSubscription();

        $this->payum->getGateway('offline')->execute(CancelSubscriptionCommand::forSubscription($subscription));

        $this->assertSame(SubscriptionStatus::Canceled, $subscription->getStatus());
        $this->assertEquals($this->now, $subscription->getCanceledAt());
    }

    public function testSyncReturnsWhatIsStored(): void
    {
        $subscription = $this->activeSubscription();
        $reference = $subscription->getReference();

        $result = $this->payum->getGateway('offline')->execute(SyncSubscriptionCommand::forSubscription($subscription));
        $this->assertInstanceOf(SubscriptionResult::class, $result);

        $this->assertSame(SubscriptionStatus::Active, $result->subscription()?->status);
        $this->assertSame($reference, $result->subscription()->reference);
        $this->assertSame($reference, $subscription->getReference());
    }

    public function testApplicationScheduledRenewalEndToEnd(): void
    {
        /** @var list<SubscriptionPaymentSucceeded> $events */
        $events = [];
        $this->dispatcher->addListener(SubscriptionPaymentSucceeded::class, static function (SubscriptionPaymentSucceeded $event) use (&$events): void {
            $events[] = $event;
        });

        $subscription = $this->subscription(SubscriptionSchedule::Application);
        $method = $subscription->getStoredPaymentMethod();
        $this->assertInstanceOf(StoredPaymentMethod::class, $method);

        $first = $this->payment($subscription);
        $token = $this->payum->prepare('offline', $first);
        $this->payum->capture(Request::create($token->getTargetUrl()));

        $this->assertSame(StoredPaymentMethodStatus::Active, $method->getStatus());
        $this->assertCount(1, $events);
        $this->assertTrue($events[0]->isFirst);

        $renewal = $this->payment($subscription);

        $result = $this->payum->getGateway('offline')->execute(CaptureCommand::forPayment($renewal));

        $this->assertSame(PaymentStatus::Captured, $result->status);
        $this->assertCount(2, $events);
        $this->assertFalse($events[1]->isFirst);
        $this->assertSame($renewal, $events[1]->payment);
    }

    private function subscription(SubscriptionSchedule $schedule): Subscription
    {
        $customer = new Customer();
        $customer->setCustomerId(uniqid('customer_'));
        $this->payum->getStorage(Customer::class)->update($customer);

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
