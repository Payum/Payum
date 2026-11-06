<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\Command\SyncCommand;
use Payum\Core\Result\PaymentStatus;

final class SyncHandlerTest extends OfflineHandlerTestCase
{
    /**
     * @return iterable<array{mixed, PaymentStatus}>
     */
    public static function provideStatuses(): iterable
    {
        yield [null, PaymentStatus::New];
        yield ['pending', PaymentStatus::Pending];
        yield ['authorized', PaymentStatus::Authorized];
        yield ['captured', PaymentStatus::Captured];
        yield ['payedout', PaymentStatus::PaidOut];
        yield ['refunded', PaymentStatus::Refunded];
        yield ['canceled', PaymentStatus::Canceled];
        yield ['some-foo-status', PaymentStatus::Unknown];
    }

    /**
     * @dataProvider provideStatuses
     */
    public function testShouldReportTheStatusInTheDetails(mixed $status, PaymentStatus $expected): void
    {
        $result = $this->gateway()->execute(SyncCommand::forPayment($this->payment([
            'status' => $status,
        ])));

        $this->assertSame($expected, $result->status);
    }
}
