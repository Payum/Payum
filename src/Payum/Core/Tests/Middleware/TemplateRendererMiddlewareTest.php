<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Middleware\TemplateRenderMiddleware;
use Payum\Core\Model\Payment;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\Result;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class TemplateRendererMiddlewareTest extends TestCase
{
    public function testShouldAddContextToTemplateRenderer(): void
    {
        $payment = new Payment();
        $gateway = $this->createStub(PaymentGateway::class);
        $token = $this->createStub(TokenInterface::class);
        $command = CaptureCommand::forPayment($payment);
        $context = $this->context($payment, $gateway, $command, $token);
        $renderTemplate = new RenderTemplate('@PayumCore/capture.html.twig');
        $this->dispatch($context, static fn (): CaptureResult => CaptureResult::pending($renderTemplate));

        $this->assertSame([
            'context' => $context,
            'gateway' => $gateway,
            'command' => $command,
            'subject' => $payment,
            'token' => $token,
        ], $renderTemplate->context);
    }

    public function testShouldNotOverrideDefaultContext(): void
    {
        $payment = new Payment();
        $gateway = $this->createStub(PaymentGateway::class);
        $token = $this->createStub(TokenInterface::class);
        $command = CaptureCommand::forPayment($payment);
        $context = $this->context($payment, $gateway, $command, $token);
        $renderTemplate = new RenderTemplate('@PayumCore/capture.html.twig', [
            'context' => 'foo',
        ]);
        $this->dispatch($context, static fn (): CaptureResult => CaptureResult::pending($renderTemplate));

        $this->assertSame([
            'context' => 'foo',
            'gateway' => $gateway,
            'command' => $command,
            'subject' => $payment,
            'token' => $token,
        ], $renderTemplate->context);
    }

    public function testShouldTakeTheSubjectFromTheContextRatherThanTheCommand(): void
    {
        $payment = new Payment();
        $token = $this->createStub(TokenInterface::class);
        $command = CaptureCommand::forToken($token);
        $context = $this->context($payment, $this->createStub(PaymentGateway::class), $command, $token);
        $renderTemplate = new RenderTemplate('@PayumCore/capture.html.twig');
        $this->dispatch($context, static fn (): CaptureResult => CaptureResult::pending($renderTemplate));

        $this->assertNotInstanceOf(SubjectInterface::class, $command->subject());
        $this->assertSame($payment, $renderTemplate->context['subject']);
    }

    public function testShouldLeaveANextActionThatIsNotATemplateAlone(): void
    {
        $payment = new Payment();
        $command = CaptureCommand::forPayment($payment);
        $context = $this->context($payment, $this->createStub(PaymentGateway::class), $command);
        $redirect = new Redirect('https://acme.test/checkout');

        $result = $this->dispatch($context, static fn (): CaptureResult => CaptureResult::pending($redirect));

        $this->assertSame($redirect, $result->next);
        $this->assertSame('https://acme.test/checkout', $result->next->url);
    }

    public function testShouldLeaveAFinishedResultAlone(): void
    {
        $payment = new Payment();
        $command = CaptureCommand::forPayment($payment);
        $context = $this->context($payment, $this->createStub(PaymentGateway::class), $command);

        $result = $this->dispatch($context, static fn (): CaptureResult => CaptureResult::captured('txn_1'));

        $this->assertNull($result->next);
    }

    public function testShouldPassANullTokenWhenTheContextHasNone(): void
    {
        $payment = new Payment();
        $gateway = $this->createStub(PaymentGateway::class);
        $command = CaptureCommand::forPayment($payment);
        $context = $this->context($payment, $gateway, $command);
        $renderTemplate = new RenderTemplate('@PayumCore/capture.html.twig');
        $this->dispatch($context, static fn (): CaptureResult => CaptureResult::pending($renderTemplate));

        $this->assertArrayHasKey('token', $renderTemplate->context);
        $this->assertNull($renderTemplate->context['token']);
    }

    public function testShouldLeaveTheTemplateNameAlone(): void
    {
        $payment = new Payment();
        $command = CaptureCommand::forPayment($payment);
        $context = $this->context($payment, $this->createStub(PaymentGateway::class), $command);
        $renderTemplate = new RenderTemplate('@PayumAcme/obtain_token.html.twig');
        $this->dispatch($context, static fn (): CaptureResult => CaptureResult::pending($renderTemplate));

        $this->assertSame('@PayumAcme/obtain_token.html.twig', $renderTemplate->template);
    }

    private function dispatch(Context $context, callable $handler): mixed
    {
        return (new Pipeline([new TemplateRenderMiddleware()]))->process(
            $context->command(),
            $context,
            $handler,
        );
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function context(Payment $payment, PaymentGateway $gateway, CommandInterface $command, ?TokenInterface $token = null): Context
    {
        return new Context(
            $this->createStub(Gateway::class),
            $command,
            $gateway,
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(GenericTokenFactoryInterface::class),
            $payment,
            $token,
        );
    }
}
