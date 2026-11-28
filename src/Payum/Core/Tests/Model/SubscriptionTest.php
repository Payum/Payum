<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Model;

use DateTimeImmutable;
use InvalidArgumentException;
use Payum\Core\Model\BillingInterval;
use Payum\Core\Model\IntervalUnit;
use Payum\Core\Model\Subscription;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Result\SubscriptionDetails;
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

    public function testAMalformedStoredIntervalReadsAsNone(): void
    {
        foreach (['bogus', '1 months', '0 day'] as $stored) {
            $subscription = new Subscription();
            (new ReflectionProperty(Subscription::class, 'interval'))->setValue($subscription, $stored);

            $this->assertNull($subscription->getInterval(), $stored);
        }
    }

    public function testFromStringIsStrict(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BillingInterval::fromString('abc');
    }

    public function testMoneyComesFromTheAmountAndCurrency(): void
    {
        $subscription = new Subscription();
        $subscription->setTotalAmount(1000);
        $subscription->setCurrencyCode('EUR');

        $this->assertSame('1000', $subscription->getMoney()?->getAmount());
    }

    public function testApplyShouldWriteOnlyTheFieldsTheDetailsCarry(): void
    {
        $periodEnd = new DateTimeImmutable('2026-12-01');
        $cancelAt = new DateTimeImmutable('2026-12-31');
        $subscription = new Subscription();
        $subscription->apply(new SubscriptionDetails(SubscriptionStatus::Active, 'sub_1', $periodEnd, $cancelAt));

        $subscription->apply(new SubscriptionDetails(canceledAt: $cancelAt));

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertSame('sub_1', $subscription->getReference());
        $this->assertSame($periodEnd, $subscription->getCurrentPeriodEnd());
        $this->assertSame($cancelAt, $subscription->getCancelAt());
        $this->assertSame($cancelAt, $subscription->getCanceledAt());

        $subscription->apply(new SubscriptionDetails());

        $this->assertSame(SubscriptionStatus::Active, $subscription->getStatus());
        $this->assertSame('sub_1', $subscription->getReference());
    }
}
