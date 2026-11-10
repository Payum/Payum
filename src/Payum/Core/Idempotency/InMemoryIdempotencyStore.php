<?php

declare(strict_types=1);

namespace Payum\Core\Idempotency;

use Payum\Core\Clock\SystemClock;
use Payum\Core\Model\IdempotencyRecord;
use Psr\Clock\ClockInterface;

/**
 * Remembers keys for as long as the PHP process lives, so a repeat in a later HTTP request is not
 * recognised. The default only so that keys work with nothing configured.
 */
final class InMemoryIdempotencyStore implements IdempotencyStoreInterface
{
    /**
     * @var array<string, IdempotencyRecord>
     */
    private array $records = [];

    public function __construct(
        private readonly ClockInterface $clock = new SystemClock()
    ) {
    }

    public function find(string $scope): ?IdempotencyRecord
    {
        return $this->records[$scope] ?? null;
    }

    public function save(IdempotencyRecord $record): void
    {
        $now = $this->clock->now();

        // A long-running worker would otherwise keep every key it ever saw.
        foreach ($this->records as $scope => $kept) {
            if ($kept->getExpiresAt() <= $now) {
                unset($this->records[$scope]);
            }
        }

        $this->records[$record->getScope()] = $record;
    }
}
