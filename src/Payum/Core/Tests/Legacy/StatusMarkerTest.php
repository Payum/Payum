<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Legacy\StatusMarker;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Result\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class StatusMarkerTest extends TestCase
{
    /**
     * @return iterable<string, array{PaymentStatus|null, string}>
     */
    public static function provideStatuses(): iterable
    {
        yield 'new' => [PaymentStatus::New, GetHumanStatus::STATUS_NEW];
        yield 'pending' => [PaymentStatus::Pending, GetHumanStatus::STATUS_PENDING];
        yield 'authorized' => [PaymentStatus::Authorized, GetHumanStatus::STATUS_AUTHORIZED];
        yield 'captured' => [PaymentStatus::Captured, GetHumanStatus::STATUS_CAPTURED];
        yield 'partially captured' => [PaymentStatus::PartiallyCaptured, GetHumanStatus::STATUS_CAPTURED];
        yield 'refunded' => [PaymentStatus::Refunded, GetHumanStatus::STATUS_REFUNDED];
        yield 'partially refunded' => [PaymentStatus::PartiallyRefunded, GetHumanStatus::STATUS_REFUNDED];
        yield 'paid out' => [PaymentStatus::PaidOut, GetHumanStatus::STATUS_PAYEDOUT];
        yield 'canceled' => [PaymentStatus::Canceled, GetHumanStatus::STATUS_CANCELED];
        yield 'failed' => [PaymentStatus::Failed, GetHumanStatus::STATUS_FAILED];
        yield 'expired' => [PaymentStatus::Expired, GetHumanStatus::STATUS_EXPIRED];
        yield 'suspended' => [PaymentStatus::Suspended, GetHumanStatus::STATUS_SUSPENDED];
        yield 'unknown' => [PaymentStatus::Unknown, GetHumanStatus::STATUS_UNKNOWN];
        yield 'not tracked' => [null, GetHumanStatus::STATUS_UNKNOWN];
    }

    /**
     * 1.x has no word for either, and none of its marks is true of both a payment at risk and one whose
     * money is gone.
     *
     * @return iterable<string, array{PaymentStatus}>
     */
    public static function provideStatusesWithoutAOneXMark(): iterable
    {
        yield 'disputed' => [PaymentStatus::Disputed];
        yield 'charged back' => [PaymentStatus::ChargedBack];
    }

    /**
     * @dataProvider provideStatuses
     */
    public function testShouldMarkTheOneXStatusThatMeansTheSame(?PaymentStatus $status, string $expected): void
    {
        StatusMarker::mark($request = new GetHumanStatus([]), $status);

        $this->assertSame($expected, $request->getValue());
    }

    /**
     * @dataProvider provideStatusesWithoutAOneXMark
     */
    public function testShouldDegradeToUnknownWhenOneXHasNoEquivalent(PaymentStatus $status): void
    {
        StatusMarker::mark($request = new GetHumanStatus([]), $status);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkEveryStatus(): void
    {
        $covered = array_merge(
            array_column(iterator_to_array(self::provideStatuses()), 0),
            array_column(iterator_to_array(self::provideStatusesWithoutAOneXMark()), 0),
        );

        foreach (PaymentStatus::cases() as $status) {
            $this->assertContains($status, $covered, sprintf('%s has no 1.x mapping under test.', $status->name));
        }
    }
}
