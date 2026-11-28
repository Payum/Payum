<?php

declare(strict_types=1);

namespace Payum\Core\Model;

use Payum\Core\Exception\InvalidArgumentException;

final class BillingInterval
{
    public function __construct(
        public readonly IntervalUnit $unit,
        public readonly int $count = 1,
    ) {
        if ($count < 1) {
            throw new InvalidArgumentException('A billing interval must span at least one unit.');
        }
    }

    /**
     * @param string $interval for example '1 month' or '2 week'
     */
    public static function fromString(string $interval): self
    {
        if (1 !== preg_match('/^(\d+) (day|week|month|year)$/', $interval, $matches)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a billing interval such as "1 month".', $interval));
        }

        return new self(IntervalUnit::from($matches[2]), (int) $matches[1]);
    }

    public function toString(): string
    {
        return $this->count . ' ' . $this->unit->value;
    }
}
