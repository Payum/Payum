<?php

declare(strict_types=1);

namespace Payum\Offline\Tests\Handler;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Result\PaymentStatus;

final class AuthorizeHandlerTest extends OfflineHandlerTestCase
{
    public function testShouldAuthorizeAPaidPayment(): void
    {
        $payment = $this->payment();

        $result = $this->gateway()->execute(AuthorizeCommand::forPayment($payment));

        $this->assertSame(PaymentStatus::Authorized, $result->status);
        $this->assertSame('authorized', $payment->getDetails()['status']);
        $this->assertSame(123, $payment->getDetails()['amount']);
    }

    public function testShouldLeaveAnUnpaidPaymentPending(): void
    {
        $result = $this->gateway()->execute(AuthorizeCommand::forPayment($this->payment([
            'paid' => false,
        ])));

        $this->assertSame(PaymentStatus::Pending, $result->status);
    }

    public function testShouldRefuseAPartialAuthorization(): void
    {
        $this->expectException(LogicException::class);

        $this->gateway()->execute(AuthorizeCommand::forPayment($this->payment(), 50));
    }
}
