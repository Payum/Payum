<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use DateInterval;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Exception\IdempotencyKeyReusedException;
use Payum\Core\Handler\Context;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Idempotency\ResultNormalizer;
use Payum\Core\Model\IdempotencyRecord;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Failure;
use Payum\Core\Result\Result;
use Psr\Clock\ClockInterface;

/**
 * Answers a repeat of an idempotency key with the result the key got the first time, without running
 * the command again.
 *
 * Only finished results are kept. One that sends the customer somewhere has a second leg still to run,
 * and a failure worth retrying would otherwise be repeated back forever.
 */
final class IdempotencyMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages identifies a payment handed to a command directly,
     *                                                       so a key reused for another payment is caught
     * @param int $ttl seconds a key is remembered for
     */
    public function __construct(
        private readonly IdempotencyStoreInterface $store,
        private readonly ClockInterface $clock,
        private readonly ?StorageRegistryInterface $storages = null,
        private readonly int $ttl = 86400,
    ) {
    }

    /**
     * Inside LockingMiddleware, so a repeat that waited for the lock finds the first result saved, and
     * outside the extensions and PersistStateMiddleware, so a replay writes nothing.
     */
    public static function priority(): int
    {
        return 800;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $key = $context->idempotencyKey();

        if (null === $key) {
            return $next($command, $context);
        }

        $scope = hash('sha256', $context->gatewayName() . "\0" . $key);
        $fingerprint = $this->fingerprintOf($command);
        $now = $this->clock->now();
        $record = $this->store->find($scope);

        if ($record instanceof IdempotencyRecord && $record->getExpiresAt() > $now) {
            if ($record->getFingerprint() !== $fingerprint) {
                throw new IdempotencyKeyReusedException(sprintf(
                    'The idempotency key "%s" was already used for a different operation. Use a new key for each operation, and the same key only to retry it.',
                    $key,
                ));
            }

            return ResultNormalizer::denormalize($record->getResult());
        }

        $result = $next($command, $context);
        $normalized = ResultNormalizer::normalize($result);

        if (null !== $normalized && ! ($result->failure instanceof Failure && $result->failure->isRetriable())) {
            $record = new IdempotencyRecord();
            $record->setScope($scope);
            $record->setFingerprint($fingerprint);
            $record->setResult($normalized);
            $record->setExpiresAt($now->add(new DateInterval('PT' . $this->ttl . 'S')));

            $this->store->save($record);
        }

        return $result;
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function fingerprintOf(CommandInterface $command): string
    {
        return hash('sha256', (string) json_encode([
            $command::class,
            SubjectIdentity::of($command, $this->storages),
            match (true) {
                $command instanceof CaptureCommand, $command instanceof AuthorizeCommand => $command->amount,
                $command instanceof RefundCommand => [$command->amount, $command->reason],
                $command instanceof CancelCommand => $command->reason,
                default => null,
            },
        ]));
    }
}
