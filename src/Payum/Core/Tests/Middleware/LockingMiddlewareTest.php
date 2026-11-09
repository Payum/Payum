<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Exception\ConcurrentOperationException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;

final class LockingMiddlewareTest extends TestCase
{
    private LockFactory $locks;

    protected function setUp(): void
    {
        $this->locks = new LockFactory(new InMemoryStore());
    }

    public function testShouldRefuseASecondExecutionForTheSamePaymentWhileTheFirstRuns(): void
    {
        $first = $this->middleware();
        $second = $this->middleware();
        $command = CaptureCommand::forToken($this->tokenFor(new Identity('p1', Payment::class)));
        $handled = 0;

        $this->expectException(ConcurrentOperationException::class);

        $this->dispatch($first, $command, function () use ($second, $command, &$handled): Result {
            ++$handled;

            return $this->dispatch($second, $command, static function () use (&$handled): Result {
                ++$handled;

                return CaptureResult::captured();
            });
        });
    }

    public function testShouldLetANestedCommandForTheSamePaymentThrough(): void
    {
        $middleware = $this->middleware();
        $token = $this->tokenFor(new Identity('p1', Payment::class));

        $result = $this->dispatch($middleware, CaptureCommand::forToken($token), fn (): Result => $this->dispatch(
            $middleware,
            SyncCommand::forToken($token),
            static fn (): Result => CaptureResult::captured(),
        ));

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldReleaseTheLockWhenTheHandlerThrows(): void
    {
        $command = CaptureCommand::forToken($this->tokenFor(new Identity('p1', Payment::class)));

        try {
            $this->dispatch($this->middleware(), $command, static function (): Result {
                throw new RuntimeException('PSP down');
            });
        } catch (RuntimeException) {
        }

        $result = $this->dispatch($this->middleware(), $command, static fn (): Result => CaptureResult::captured());

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldNotHoldUpADifferentPayment(): void
    {
        $second = $this->middleware();

        $result = $this->dispatch(
            $this->middleware(),
            CaptureCommand::forToken($this->tokenFor(new Identity('p1', Payment::class))),
            fn (): Result => $this->dispatch(
                $second,
                CaptureCommand::forToken($this->tokenFor(new Identity('p2', Payment::class))),
                static fn (): Result => CaptureResult::captured(),
            ),
        );

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldLockAPaymentHandedOverDirectlyUnderTheSameKeyAsItsToken(): void
    {
        $payment = new Payment();
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('support')->willReturn(true);
        $storage->method('identify')->willReturn(new Identity('p1', Payment::class));
        $storages = $this->createMock(StorageRegistryInterface::class);
        $storages->method('getStorages')->willReturn([
            Payment::class => $storage,
        ]);

        $second = $this->middleware();

        $this->expectException(ConcurrentOperationException::class);

        $this->dispatch(
            new LockingMiddleware($this->locks, $storages, 0.0),
            CaptureCommand::forPayment($payment),
            fn (): Result => $this->dispatch(
                $second,
                CaptureCommand::forToken($this->tokenFor(new Identity('p1', Payment::class))),
                static fn (): Result => CaptureResult::captured(),
            ),
        );
    }

    public function testShouldNotLockAPaymentThatWasNeverSaved(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('support')->willReturn(true);
        $storage->method('identify')->willThrowException(new LogicException('The model must be persisted before usage of this method'));
        $storages = $this->createMock(StorageRegistryInterface::class);
        $storages->method('getStorages')->willReturn([
            Payment::class => $storage,
        ]);
        $payment = new Payment();

        $second = new LockingMiddleware($this->locks, $storages, 0.0);

        $result = $this->dispatch(
            new LockingMiddleware($this->locks, $storages, 0.0),
            CaptureCommand::forPayment($payment),
            fn (): Result => $this->dispatch($second, CaptureCommand::forPayment($payment), static fn (): Result => CaptureResult::captured()),
        );

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldNotLockANotifyThatPointsAtNothing(): void
    {
        $second = $this->middleware();

        $result = $this->dispatch(
            $this->middleware(),
            NotifyCommand::forGateway(),
            fn (): Result => $this->dispatch($second, NotifyCommand::forGateway(), static fn (): Result => CaptureResult::captured()),
        );

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldLockANotifyThatArrivedOnAToken(): void
    {
        $token = $this->tokenFor(new Identity('p1', Payment::class));
        $second = $this->middleware();

        $this->expectException(ConcurrentOperationException::class);
        $this->expectExceptionMessage(CaptureCommand::class);

        $this->dispatch(
            $this->middleware(),
            NotifyCommand::forToken($token),
            fn (): Result => $this->dispatch($second, CaptureCommand::forToken($token), static fn (): Result => CaptureResult::captured()),
        );
    }

    private function middleware(): LockingMiddleware
    {
        return new LockingMiddleware($this->locks, null, 0.0);
    }

    private function tokenFor(Identity $identity): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getDetails')->willReturn($identity);

        return $token;
    }

    /**
     * @param CommandInterface<Result> $command
     * @param callable(): Result $handler
     */
    private function dispatch(LockingMiddleware $middleware, CommandInterface $command, callable $handler): Result
    {
        return (new Pipeline([$middleware]))->process(
            $command,
            new Context(
                $this->createMock(Gateway::class),
                $command,
                $this->createMock(PaymentGateway::class),
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(TokenIssuerInterface::class),
            ),
            static fn (): Result => $handler(),
        );
    }
}
