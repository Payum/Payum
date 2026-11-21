<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Model;

use DateTimeImmutable;
use InvalidArgumentException;
use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\IntervalUnit;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Result\SubscriptionStatus;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

final class SubscriptionTest extends TestCase
{
    public function testANewSubscriptionIsPending(): void
    {
        $subscription = new Subscription();

        $this->assertSame(SubscriptionStatus::Pending, $subscription->getStatus());
        $this->assertSame(SubscriptionSchedule::Gateway, $subscription->getSchedule());
        $this->assertNull($subscription->getMoney());
        $this->assertNull($subscription->getInterval());
    }

    public function testAnUnrecognisedStoredStatusIsUnknown(): void
    {
        $subscription = new Subscription();
        $property = new ReflectionProperty(Subscription::class, 'status');
        $property->setValue($subscription, 'bogus');

        $this->assertSame(SubscriptionStatus::Unknown, $subscription->getStatus());
    }

    public function testIntervalShouldRoundTripAsAString(): void
    {
        $subscription = new Subscription();
        $subscription->setInterval(new BillingInterval(IntervalUnit::Week, 2));

        $this->assertSame('2 week', (new ReflectionProperty(Subscription::class, 'interval'))->getValue($subscription));
        $this->assertSame(IntervalUnit::Week, $subscription->getInterval()?->unit);
        $this->assertSame(2, $subscription->getInterval()->count);
        $this->assertSame('1 month', BillingInterval::fromString('1 month')->toString());
    }

    public function testIntervalShouldRefuseAZeroCount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BillingInterval(IntervalUnit::Day, 0);
    }

    public function testMoneyComesFromTheAmountAndCurrency(): void
    {
        $subscription = new Subscription();
        $subscription->setTotalAmount(1000);
        $subscription->setCurrencyCode('EUR');
        $subscription->setStartAt(new DateTimeImmutable('2026-01-01'));

        $this->assertSame('1000', $subscription->getMoney()?->getAmount());
    }
}
