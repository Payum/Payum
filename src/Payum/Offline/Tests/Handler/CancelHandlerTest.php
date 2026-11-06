<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Result\PaymentStatus;

final class CancelHandlerTest extends OfflineHandlerTestCase
{
    public function testShouldCancelAPendingPayment(): void
    {
        $payment = $this->payment([
            'status' => 'pending',
        ]);

        $result = $this->gateway()->execute(CancelCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Canceled, $result->status);
        $this->assertSame('canceled', $payment->getDetails()['status']);
    }

    public function testShouldDeclineToCancelACapturedPayment(): void
    {
        $payment = $this->payment([
            'status' => 'captured',
        ]);

        $result = $this->gateway()->execute(CancelCommand::forPayment($payment));

        $this->assertSame('not_cancelable', $result->failure?->code);
        $this->assertSame('captured', $payment->getDetails()['status']);
    }
}
