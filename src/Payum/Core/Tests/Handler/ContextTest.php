<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Money\Money;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway as Executor;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\Result;
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

    public function testShouldAnswerTheSubjectsFullAmountWhenTheCommandAskedForNothing(): void
    {
        $context = $this->buildContextFor(CaptureCommand::forToken($this->createMock(TokenInterface::class)));

        $this->assertTrue(Money::USD(1000)->equals($context->amount()));
    }

    public function testShouldAnswerWhatAPartialCommandAskedFor(): void
    {
        $context = $this->buildContextFor(CaptureCommand::forToken($this->createMock(TokenInterface::class), 400));

        $this->assertTrue(Money::USD(400)->equals($context->amount()));
    }

    public function testShouldReadAPartialAmountInTheResolvedSubjectsCurrency(): void
    {
        // The command carries only a token, so the currency can come from nowhere but the subject core
        // resolved.
        $context = $this->buildContextFor(RefundCommand::forToken($this->createMock(TokenInterface::class), 400));

        $this->assertSame('USD', $context->amount()?->getCurrency()->getCode());
    }

    public function testShouldAnswerNothingForACommandThatCarriesNoAmountAndASubjectWithout(): void
    {
        $context = $this->buildContextFor(
            CancelCommand::forPayment(new Payment()),
            new Payment(),
        );

        $this->assertNull($context->amount());
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

    /**
     * @param CommandInterface<Result> $command
     */
    private function buildContextFor(CommandInterface $command, ?PaymentInterface $subject = null): Context
    {
        if (! $subject instanceof PaymentInterface) {
            $subject = new Payment();
            $subject->setTotalAmount(1000);
            $subject->setCurrencyCode('USD');
        }

        return new Context(
            $this->createMock(Executor::class),
            $command,
            $this->createMock(PaymentGateway::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(GenericTokenFactoryInterface::class),
            $subject,
        );
    }
}
