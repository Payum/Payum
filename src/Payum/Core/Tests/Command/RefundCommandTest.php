<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Command;

use Payum\Core\Command\RefundCommand;
use Payum\Core\Model\Payment;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

final class RefundCommandTest extends TestCase
{
    public function testShouldCarryTheIdempotencyKeyWhenBuiltFromAPayment(): void
    {
        $payment = new Payment();

        $command = RefundCommand::forPayment($payment, 500, 'requested_by_customer', 'refund-42');

        $this->assertSame($payment, $command->payment());
        $this->assertSame(500, $command->amount);
        $this->assertSame('requested_by_customer', $command->reason);
        $this->assertSame('refund-42', $command->idempotencyKey);
    }

    public function testShouldCarryTheIdempotencyKeyWhenBuiltFromAToken(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $command = RefundCommand::forToken($token, idempotencyKey: 'refund-42');

        $this->assertSame($token, $command->token());
        $this->assertSame('refund-42', $command->idempotencyKey);
    }

    public function testShouldCarryNoIdempotencyKeyByDefault(): void
    {
        $this->assertNull(RefundCommand::forPayment(new Payment())->idempotencyKey);
        $this->assertNull(RefundCommand::forToken($this->createMock(TokenInterface::class))->idempotencyKey);
    }
}
