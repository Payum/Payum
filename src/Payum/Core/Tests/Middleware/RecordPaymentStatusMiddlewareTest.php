<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use DI\Container;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareCollection;
use Payum\Core\Middleware\PersistStateMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Middleware\RecordPaymentStatusMiddleware;
use Payum\Core\Model\HasPaymentStatus;
use Payum\Core\Model\Payment;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\RefundResult;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class RecordPaymentStatusMiddlewareTest extends TestCase
{
    public function testShouldRecordTheStatusTheHandlerDeclared(): void
    {
        $payment = new TrackedPayment();

        $this->dispatch($payment, static fn (): CaptureResult => CaptureResult::captured('txn_1'));

        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    public function testShouldLeaveTheStatusAloneWhenAnOperationConcludedNothing(): void
    {
        $payment = new TrackedPayment();
        $payment->setStatus(PaymentStatus::Captured);

        // A refund that was declined says nothing about the payment.
        $this->dispatch($payment, static fn (): RefundResult => RefundResult::failed(
            new Failure(FailureReason::Declined, 'refund_declined'),
        ));

        $this->assertSame(PaymentStatus::Captured, $payment->getStatus());
    }

    public function testShouldRecordATerminalFailureWhenTheHandlerSaysSo(): void
    {
        $payment = new TrackedPayment();

        $this->dispatch($payment, static fn (): CaptureResult => CaptureResult::failed(
            new Failure(FailureReason::Fraud, 'blocked'),
            PaymentStatus::Failed,
        ));

        $this->assertSame(PaymentStatus::Failed, $payment->getStatus());
    }

    public function testShouldNotRecordAnythingWhenTheHandlerThrows(): void
    {
        $payment = new TrackedPayment();
        $payment->setStatus(PaymentStatus::Pending);

        try {
            $this->dispatch($payment, static fn () => throw new RuntimeException('psp exploded'));

            $this->fail('Expected the exception to propagate.');
        } catch (RuntimeException) {
            // An exception means we did not learn the status, which is not the same as learning it failed.
            $this->assertSame(PaymentStatus::Pending, $payment->getStatus());
        }
    }

    public function testShouldDoNothingForAPaymentThatDoesNotTrackAStatus(): void
    {
        $payment = new Payment();

        $result = $this->dispatch($payment, static fn (): CaptureResult => CaptureResult::captured('txn_1'));

        $this->assertSame(PaymentStatus::Captured, $result->status);
    }

    public function testShouldSetTheStatusBeforeThePaymentIsPersisted(): void
    {
        $payment = new TrackedPayment();
        $storage = new StatusSpyStorage();

        // Resolved through the collection, so this exercises the real priorities rather than an order
        // written by hand here.
        $middleware = CoreDefaults::statusAndPersistence($storage)->resolve(new Container());

        (new Pipeline($middleware))->process(
            CaptureCommand::forToken($this->createMock(TokenInterface::class)),
            $context = $this->context($payment, $this->createMock(TokenInterface::class)),
            static function () use ($context): CaptureResult {
                // PersistState only writes when the handler touched state, as a handler would.
                $context->state()['checkout_id'] = 'chk_1';

                return CaptureResult::captured('txn_1');
            },
        );

        // Recorded further out than PersistState, the row would have been written a command too early.
        $this->assertSame(PaymentStatus::Captured, $storage->statusWhenUpdated);
    }

    private function dispatch(Payment $payment, callable $handler): mixed
    {
        return (new Pipeline([new RecordPaymentStatusMiddleware()]))->process(
            CaptureCommand::forPayment($payment),
            $this->context($payment),
            $handler,
        );
    }

    private function context(Payment $payment, ?TokenInterface $token = null): Context
    {
        return new Context(
            $this->createMock(Gateway::class),
            CaptureCommand::forPayment($payment),
            $this->createMock(PaymentGateway::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(GenericTokenFactoryInterface::class),
            $payment,
            $token,
        );
    }
}

final class CoreDefaults
{
    /**
     * @param StorageInterface<object> $storage
     */
    public static function statusAndPersistence(StorageInterface $storage): MiddlewareCollection
    {
        $registry = new class($storage) implements StorageRegistryInterface {
            /**
             * @param StorageInterface<object> $storage
             */
            public function __construct(
                private readonly StorageInterface $storage
            ) {
            }

            public function getStorage(string | object $class): StorageInterface
            {
                return $this->storage;
            }

            public function getStorages(): array
            {
                return [];
            }
        };

        // Registered status-first on purpose: priority, not registration order, has to decide.
        return (new MiddlewareCollection())
            ->with(new RecordPaymentStatusMiddleware())
            ->with(new PersistStateMiddleware($registry));
    }
}

class TrackedPayment extends Payment implements HasPaymentStatus
{
    private PaymentStatus $status = PaymentStatus::New;

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}

/**
 * @implements StorageInterface<object>
 */
final class StatusSpyStorage implements StorageInterface
{
    public ?PaymentStatus $statusWhenUpdated = null;

    public function create(): object
    {
        return new TrackedPayment();
    }

    public function support(object $model): bool
    {
        return $model instanceof TrackedPayment;
    }

    public function update(object $model): object
    {
        $this->statusWhenUpdated = $model instanceof HasPaymentStatus ? $model->getStatus() : null;

        return $model;
    }

    public function delete(object $model): void
    {
    }

    public function find($id): ?object
    {
        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<object>
     */
    public function findBy(array $criteria): array
    {
        return [];
    }

    public function identify($model): IdentityInterface
    {
        throw new LogicException('not needed');
    }
}
