<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Command;

use Payum\Core\Command\NotifyCommand;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\Payment;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

final class NotifyCommandTest extends TestCase
{
    public function testShouldExerciseTheWebhooksCapability(): void
    {
        $this->assertSame(Capability::Webhooks, NotifyCommand::capability());
    }

    public function testShouldCarryTheTokenItArrivedOn(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $command = NotifyCommand::forToken($token);

        $this->assertSame($token, $command->token());
        $this->assertNull($command->subject());
    }

    public function testShouldCarryAPaymentWhenTheCallerKnowsIt(): void
    {
        $payment = new Payment();

        $command = NotifyCommand::forPayment($payment);

        $this->assertSame($payment, $command->subject());
        $this->assertSame($payment, $command->payment());
        $this->assertNull($command->token());
    }

    public function testShouldAllowNeitherWhenTheApplicationRoutesTheEndpoint(): void
    {
        // Which payment an event belongs to is something verification works out, so unlike every other
        // command this one may point at nothing.
        $command = NotifyCommand::forGateway();

        $this->assertNull($command->token());
        $this->assertNull($command->subject());
        $this->assertNull($command->payment());
    }
}
