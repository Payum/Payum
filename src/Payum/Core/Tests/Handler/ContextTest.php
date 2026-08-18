<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway as Executor;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class ContextTest extends TestCase
{
    public function testShouldMintANotifyTokenPointedAtTheSubject(): void
    {
        $payment = new Payment();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getTargetUrl')->willReturn('https://payum.dev/notify.php?payum_token=abc');

        $tokens = $this->createMock(GenericTokenFactoryInterface::class);
        $tokens->expects($this->once())
            ->method('createNotifyToken')
            ->with('acme', $this->identicalTo($payment))
            ->willReturn($token);

        $context = $this->buildContext($tokens, $payment, 'acme');

        $this->assertSame('https://payum.dev/notify.php?payum_token=abc', $context->notifyUrl());
        $this->assertSame($token, $context->notifyToken());
    }

    public function testShouldMintOnceForOneExecution(): void
    {
        $payment = new Payment();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getTargetUrl')->willReturn('https://payum.dev/notify.php');

        $tokens = $this->createMock(GenericTokenFactoryInterface::class);
        // Calling it twice must not leave two token rows behind.
        $tokens->expects($this->once())
            ->method('createNotifyToken')
            ->willReturn($token);

        $context = $this->buildContext($tokens, $payment, 'acme');

        $context->notifyUrl();
        $context->notifyUrl();
    }

    public function testShouldMintAGatewayLevelTokenWhenThereIsNoSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);

        $tokens = $this->createMock(GenericTokenFactoryInterface::class);
        $tokens->expects($this->once())
            ->method('createNotifyToken')
            ->with('acme', null)
            ->willReturn($token);

        $this->buildContext($tokens, null, 'acme')->notifyToken();
    }

    public function testShouldExposeTheRegisteredGatewayName(): void
    {
        $context = $this->buildContext($this->createMock(GenericTokenFactoryInterface::class), null, 'acme');

        $this->assertSame('acme', $context->gatewayName());
    }

    public function testShouldRefuseToMintForAGatewayRegisteredUnderNoName(): void
    {
        $context = $this->buildContext($this->createMock(GenericTokenFactoryInterface::class), null, null);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('is not registered under a name');

        $context->notifyUrl();
    }

    private function buildContext(
        GenericTokenFactoryInterface $tokens,
        ?PaymentInterface $subject,
        ?string $gatewayName
    ): Context {
        return new Context(
            $this->createMock(Executor::class),
            CaptureCommand::forPayment(new Payment()),
            $this->createMock(PaymentGateway::class),
            $this->createMock(ServerRequestInterface::class),
            $tokens,
            $subject,
            null,
            [],
            $gatewayName,
        );
    }
}
