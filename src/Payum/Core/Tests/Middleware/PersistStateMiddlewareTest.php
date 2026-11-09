<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DI\Container;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareCollection;
use Payum\Core\Middleware\PersistStateMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Middleware\RecordPaymentStatusMiddleware;
use Payum\Core\Model\Payment;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class PersistStateMiddlewareTest extends TestCase
{
    public function testShouldSaveATokenLoadedSubjectWhoseStatusAloneChanged(): void
    {
        $payment = new Payment();
        $storage = $this->createMock(StorageInterface::class);
        $savedStatus = null;
        $storage
            ->expects($this->once())
            ->method('update')
            ->willReturnCallback(static function (Payment $saved) use (&$savedStatus): Payment {
                $savedStatus = $saved->getStatus();

                return $saved;
            })
        ;

        $this->sync($payment, $storage, static fn (): SyncResult => SyncResult::synced(PaymentStatus::Captured));

        $this->assertSame(PaymentStatus::Captured, $savedStatus);
    }

    public function testShouldNotSaveATokenLoadedSubjectNothingChanged(): void
    {
        $payment = new Payment();
        $payment->setStatus(PaymentStatus::Captured);
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->never())->method('update');

        $this->sync($payment, $storage, static fn (): SyncResult => SyncResult::synced(PaymentStatus::Captured));
    }

    public function testShouldSaveThePspRequestOfAnAcceptedOperationWithoutMovingTheStatus(): void
    {
        $payment = new Payment();
        $payment->setStatus(PaymentStatus::Captured);
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('update')->willReturnArgument(0);

        $this->sync($payment, $storage, static function (RefundCommand $command, Context $context): RefundResult {
            $context->state()['refund_id'] = 're_1';

            return RefundResult::accepted('re_1');
        }, RefundCommand::forToken($this->createMock(TokenInterface::class)));

        $this->assertSame('re_1', $payment->getDetails()['refund_id']);
        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    /**
     * @param StorageInterface<object>&MockObject $storage
     * @param CommandInterface<Result>|null $command
     */
    private function sync(Payment $payment, StorageInterface $storage, callable $handler, ?CommandInterface $command = null): void
    {
        $registry = $this->createMock(StorageRegistryInterface::class);
        $registry->method('getStorage')->willReturn($storage);

        $middleware = (new MiddlewareCollection())
            ->with(new RecordPaymentStatusMiddleware())
            ->with(new PersistStateMiddleware($registry))
            ->resolve(new Container())
        ;

        $command ??= SyncCommand::forToken($this->createMock(TokenInterface::class));
        $token = $command->token();

        (new Pipeline($middleware))->process(
            $command,
            new Context(
                $this->createMock(Gateway::class),
                $command,
                $this->createMock(PaymentGateway::class),
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(TokenIssuerInterface::class),
                $payment,
                $token,
            ),
            $handler,
        );
    }
}
