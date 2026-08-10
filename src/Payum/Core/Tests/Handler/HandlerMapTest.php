<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\RefundResult;
use PHPUnit\Framework\TestCase;

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
        $map = HandlerMap::fromHandlers([CaptureHandlerStub::class, RefundHandlerStub::class]);

        $this->assertSame([Capability::Capture, Capability::Refund], $map->capabilities());
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

final class HandlerWithoutACommand implements HandlerInterface
{
}
