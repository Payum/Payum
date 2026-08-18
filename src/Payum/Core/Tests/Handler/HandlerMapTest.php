<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Handler\CancelHandlerInterface;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Result\CancelResult;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\RefundResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class HandlerMapTest extends TestCase
{
    public function testShouldMapACommandToTheInterfaceRatherThanTheConcreteHandler(): void
    {
        $map = HandlerMap::fromHandlers([CaptureHandlerStub::class]);

        // The interface, so the container is free to decorate it.
        $this->assertSame(CaptureHandlerInterface::class, $map->serviceIdFor(CaptureCommand::class));
    }

    public function testShouldReturnNullForACommandTheGatewayDoesNotHandle(): void
    {
        $map = HandlerMap::fromHandlers([CaptureHandlerStub::class]);

        $this->assertNull($map->serviceIdFor(RefundCommand::class));
    }

    public function testShouldMapEveryHandlerItIsGiven(): void
    {
        $map = HandlerMap::fromHandlers([CaptureHandlerStub::class, RefundHandlerStub::class]);

        $this->assertSame(CaptureHandlerInterface::class, $map->serviceIdFor(CaptureCommand::class));
        $this->assertSame(RefundHandlerInterface::class, $map->serviceIdFor(RefundCommand::class));
        $this->assertSame([CaptureCommand::class, RefundCommand::class], $map->commands());
    }

    public function testShouldDeriveCapabilitiesFromTheCommandsItHandles(): void
    {
        $map = HandlerMap::fromHandlers([
            CaptureHandlerStub::class,
            RefundHandlerStub::class,
            CancelHandlerStub::class,
        ]);

        $this->assertSame(
            [Capability::Capture, Capability::Refund, Capability::Cancel],
            $map->capabilities(),
        );
    }

    public function testShouldMapCancel(): void
    {
        $map = HandlerMap::fromHandlers([CancelHandlerStub::class]);

        $this->assertSame(CancelHandlerInterface::class, $map->serviceIdFor(CancelCommand::class));
        $this->assertSame([CancelCommand::class], $map->commands());
    }

    public function testShouldBeEmptyWhenThereAreNoHandlers(): void
    {
        $map = HandlerMap::fromHandlers([]);

        $this->assertSame([], $map->commands());
        $this->assertSame([], $map->capabilities());
        $this->assertNull($map->serviceIdFor(CaptureCommand::class));
    }

    public function testShouldThrowWhenAHandlerImplementsNoHandlerInterface(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('implements no handler interface');

        HandlerMap::fromHandlers([HandlerWithoutACommand::class]);
    }

    public function testShouldThrowWhenTwoHandlersClaimTheSameCommand(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A command may only have one handler.');

        HandlerMap::fromHandlers([CaptureHandlerStub::class, AnotherCaptureHandlerStub::class]);
    }

    public function testShouldMapAHandlerWhoseHandleTakesMoreThanTwoParameters(): void
    {
        $map = HandlerMap::fromHandlers([AcmeNotifyHandler::class]);

        $this->assertSame(NotifyHandlerInterface::class, $map->serviceIdFor(NotifyCommand::class));
        $this->assertSame([NotifyCommand::class], $map->commands());
    }

    public function testShouldDeriveTheWebhooksCapabilityFromANotifyHandler(): void
    {
        $map = HandlerMap::fromHandlers([AcmeNotifyHandler::class]);

        $this->assertSame([Capability::Webhooks], $map->capabilities());
    }
}

final class CaptureHandlerStub implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return CaptureResult::captured();
    }
}

final class AnotherCaptureHandlerStub implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        return CaptureResult::captured();
    }
}

final class RefundHandlerStub implements RefundHandlerInterface
{
    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        return RefundResult::refunded();
    }
}

final class CancelHandlerStub implements CancelHandlerInterface
{
    public function handle(CancelCommand $command, Context $context): CancelResult
    {
        return CancelResult::canceled();
    }
}

final class HandlerWithoutACommand implements HandlerInterface
{
}

final class AcmeNotifyHandler implements NotifyHandlerInterface
{
    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        return NotifyResult::ignored();
    }

    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        return WebhookEvent::verified([]);
    }
}
