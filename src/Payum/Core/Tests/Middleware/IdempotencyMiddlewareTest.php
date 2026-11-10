<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Middleware;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Command\SyncCommand;
use Payum\Core\Exception\IdempotencyKeyReusedException;
use Payum\Core\Gateway;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\Context;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Idempotency\InMemoryIdempotencyStore;
use Payum\Core\Middleware\IdempotencyMiddleware;
use Payum\Core\Middleware\Pipeline;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\RefundResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenIssuerInterface;
use Payum\Core\Tests\Mocks\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class IdempotencyMiddlewareTest extends TestCase
{
    private IdempotencyStoreInterface $store;

    private TokenInterface $token;

    private int $handled = 0;

    protected function setUp(): void
    {
        $this->store = new InMemoryIdempotencyStore();
        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getDetails')->willReturn(new Identity('p1', Payment::class));
    }

    public function testShouldReturnTheFirstResultForARepeatedKey(): void
    {
        $command = CaptureCommand::forToken($this->token, 1000, 'order-1');

        $first = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1', 1000, [
            'id' => 'ch_1',
        ]));
        $second = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_2'));

        $this->assertSame(1, $this->handled);
        $this->assertSame('ch_1', $second->transactionId);
        $this->assertSame(1000, $second->capturedAmount ?? null);
        $this->assertSame(PaymentStatus::Captured, $second->status);
        $this->assertSame([
            'id' => 'ch_1',
        ], $first->raw);
        $this->assertSame([], $second->raw);
    }

    public function testShouldRunEveryTimeWithoutAKey(): void
    {
        $command = CaptureCommand::forToken($this->token);

        $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1'));
        $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_2'));

        $this->assertSame(2, $this->handled);
    }

    public function testShouldIgnoreSyncs(): void
    {
        $command = SyncCommand::forToken($this->token);

        $this->dispatch($command, static fn (): Result => SyncResult::synced(PaymentStatus::Captured));
        $this->dispatch($command, static fn (): Result => SyncResult::synced(PaymentStatus::Captured));

        $this->assertSame(2, $this->handled);
    }

    public function testShouldRefuseAKeyReusedForADifferentAmount(): void
    {
        $this->dispatch(CaptureCommand::forToken($this->token, 1000, 'order-1'), static fn (): Result => CaptureResult::captured('ch_1'));

        $this->expectException(IdempotencyKeyReusedException::class);
        $this->expectExceptionMessage('order-1');

        $this->dispatch(CaptureCommand::forToken($this->token, 500, 'order-1'), static fn (): Result => CaptureResult::captured('ch_2'));
    }

    public function testShouldRefuseAKeyReusedForADifferentOperation(): void
    {
        $this->dispatch(CaptureCommand::forToken($this->token, null, 'order-1'), static fn (): Result => CaptureResult::captured('ch_1'));

        $this->expectException(IdempotencyKeyReusedException::class);

        $this->dispatch(RefundCommand::forToken($this->token, null, null, 'order-1'), static fn (): Result => RefundResult::refunded('re_1'));
    }

    public function testShouldRefuseAKeyReusedForADifferentPayment(): void
    {
        $other = $this->createMock(TokenInterface::class);
        $other->method('getDetails')->willReturn(new Identity('p2', Payment::class));

        $this->dispatch(CaptureCommand::forToken($this->token, null, 'order-1'), static fn (): Result => CaptureResult::captured('ch_1'));

        $this->expectException(IdempotencyKeyReusedException::class);

        $this->dispatch(CaptureCommand::forToken($other, null, 'order-1'), static fn (): Result => CaptureResult::captured('ch_2'));
    }

    public function testShouldRunTheReturnLegOfARedirectAgain(): void
    {
        $command = CaptureCommand::forToken($this->token, null, 'order-1');

        $this->dispatch($command, static fn (): Result => CaptureResult::pending(new Redirect('https://acme.test/checkout')));
        $result = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1'));
        $replayed = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_2'));

        $this->assertSame(2, $this->handled);
        $this->assertSame('ch_1', $result->transactionId);
        $this->assertSame('ch_1', $replayed->transactionId);
    }

    public function testShouldLetARetriableFailureBeRetried(): void
    {
        $command = CaptureCommand::forToken($this->token, null, 'order-1');

        $this->dispatch($command, static fn (): Result => CaptureResult::failed(new Failure(FailureReason::Network, 'timeout')));
        $result = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1'));

        $this->assertSame(2, $this->handled);
        $this->assertSame('ch_1', $result->transactionId);
    }

    public function testShouldReplayADecline(): void
    {
        $command = CaptureCommand::forToken($this->token, null, 'order-1');

        $this->dispatch($command, static fn (): Result => CaptureResult::failed(new Failure(FailureReason::Declined, 'card_declined')));
        $result = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1'));

        $this->assertSame(1, $this->handled);
        $this->assertSame(FailureReason::Declined, $result->failure?->reason);
    }

    public function testShouldForgetAKeyOnceItExpires(): void
    {
        $command = CaptureCommand::forToken($this->token, null, 'order-1');

        $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1'), new FrozenClock('2026-11-10 12:00:00'));
        $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_2'), new FrozenClock('2026-11-11 11:59:59'));
        $result = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_3'), new FrozenClock('2026-11-11 12:00:00'));

        $this->assertSame(2, $this->handled);
        $this->assertSame('ch_3', $result->transactionId);
    }

    public function testShouldKeepEachGatewaysKeysApart(): void
    {
        $command = CaptureCommand::forToken($this->token, null, 'order-1');

        $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_1'), null, 'stripe');
        $result = $this->dispatch($command, static fn (): Result => CaptureResult::captured('ch_2'), null, 'paypal');

        $this->assertSame(2, $this->handled);
        $this->assertSame('ch_2', $result->transactionId);
    }

    /**
     * @param CommandInterface<Result> $command
     * @param callable(): Result $handler
     */
    private function dispatch(CommandInterface $command, callable $handler, ?FrozenClock $clock = null, string $gatewayName = 'acme'): Result
    {
        $middleware = new IdempotencyMiddleware($this->store, $clock ?? new FrozenClock('2026-11-10 12:00:00'));

        return (new Pipeline([$middleware]))->process(
            $command,
            new Context(
                $this->createMock(Gateway::class),
                $command,
                $this->createMock(PaymentGateway::class),
                $this->createMock(ServerRequestInterface::class),
                $this->createMock(TokenIssuerInterface::class),
                null,
                $command->token(),
                [],
                $gatewayName,
            ),
            function () use ($handler): Result {
                ++$this->handled;

                return $handler();
            },
        );
    }
}
