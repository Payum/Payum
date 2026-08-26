<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class FrozenClock implements ClockInterface
{
    private readonly DateTimeImmutable $now;

    public function __construct(DateTimeImmutable | string $now = 'now')
    {
        $this->now = $now instanceof DateTimeImmutable ? $now : new DateTimeImmutable($now);
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
