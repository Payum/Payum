<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\ConcurrentOperationException;
use Payum\Core\Handler\Context;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Lets one execution at a time work on a given payment or payout.
 *
 * The lock is keyed on the stored identity, never on the token: a capture token and a notify token point
 * at the same payment, and the race worth stopping is between the two.
 */
final class LockingMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @var array<string, true>
     */
    private array $held = [];

    /**
     * @param StorageRegistryInterface<object> $storages identifies a payment handed to a command directly
     * @param float $timeout seconds to wait for another execution to finish before giving up
     * @param float $ttl seconds after which a store that expires locks releases one whose holder died
     */
    public function __construct(
        private readonly LockFactory $locks,
        private readonly StorageRegistryInterface $storages,
        private readonly float $timeout = 10.0,
        private readonly float $ttl = 300.0,
    ) {
    }

    /**
     * Inside EndlessCycleDetectorMiddleware, and outside the extensions and PersistStateMiddleware so
     * that everything they write happens while the lock is held.
     */
    public static function priority(): int
    {
        return 900;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $identity = SubjectIdentity::of($command, $this->storages);
        $key = null === $identity ? null : 'payum:' . $identity;

        // A handler dispatching a sub-command about the same payment already holds the lock.
        if (null === $key || isset($this->held[$key])) {
            return $next($command, $context);
        }

        $lock = $this->locks->createLock($key, $this->ttl);
        $this->acquire($lock, $command, $key);
        $this->held[$key] = true;

        try {
            return $next($command, $context);
        } finally {
            unset($this->held[$key]);
            $lock->release();
        }
    }

    /**
     * @param CommandInterface<Result> $command
     *
     * @throws ConcurrentOperationException
     */
    private function acquire(LockInterface $lock, CommandInterface $command, string $key): void
    {
        $deadline = microtime(true) + $this->timeout;

        while (! $lock->acquire()) {
            $remaining = $deadline - microtime(true);

            if ($remaining <= 0) {
                throw new ConcurrentOperationException(sprintf(
                    'Another execution is still working on %s, so %s gave up after waiting %s seconds. Nothing was sent to the PSP; retry the command.',
                    $key,
                    $command::class,
                    $this->timeout,
                ));
            }

            usleep((int) (min($remaining, 0.05) * 1_000_000));
        }
    }
}
