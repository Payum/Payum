<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Command;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

final class CancelCommandTest extends TestCase
{
    public function testShouldExerciseTheCancelCapability(): void
    {
        $this->assertSame(Capability::Cancel, CancelCommand::capability());
    }

    public function testShouldBeBuiltFromAPayment(): void
    {
        $payment = new Payment();

        $command = CancelCommand::forPayment($payment, 'merchant_abandoned');

        $this->assertSame($payment, $command->payment());
        $this->assertNotInstanceOf(TokenInterface::class, $command->token());
        $this->assertSame('merchant_abandoned', $command->reason);
    }

    public function testShouldBeBuiltFromAToken(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $command = CancelCommand::forToken($token);

        $this->assertSame($token, $command->token());
        $this->assertNotInstanceOf(PaymentInterface::class, $command->payment());
    }

    public function testShouldRefuseToBeBuiltWithNothingToCancel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('needs either a token or a payment');

        new CancelCommand();
    }

    public function testShouldReportCanceled(): void
    {
        $result = CancelResult::canceled('txn_1');

        $this->assertSame(PaymentStatus::Canceled, $result->status);
        $this->assertSame('txn_1', $result->transactionId);
        $this->assertNotInstanceOf(NextAction::class, $result->next);
    }

    public function testShouldSayNothingAboutTheStatusWhenTheCancelIsRefused(): void
    {
        // A cancel the PSP refused leaves the payment as it was, usually still authorized.
        $result = CancelResult::failed(new Failure(FailureReason::Declined, 'already_captured'));

        $this->assertNotInstanceOf(PaymentStatus::class, $result->status);
        $this->assertTrue($result->isFailed());
    }

    public function testShouldReportATerminalFailureWhenTheHandlerSaysSo(): void
    {
        $result = CancelResult::failed(
            new Failure(FailureReason::Configuration, 'bad_key'),
            PaymentStatus::Failed,
        );

        $this->assertSame(PaymentStatus::Failed, $result->status);
    }
}
