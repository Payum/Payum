<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Payum\Core\Clock\SystemClock;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class SystemClockTest extends TestCase
{
    public function testShouldImplementClockInterface(): void
    {
        $this->assertInstanceOf(ClockInterface::class, new SystemClock());
    }

    public function testShouldReturnTheCurrentTime(): void
    {
        $before = new DateTimeImmutable();
        $now = (new SystemClock())->now();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $now);
        $this->assertLessThanOrEqual($after, $now);
    }

    public function testShouldReturnADistinctInstanceOnEveryCall(): void
    {
        $clock = new SystemClock();

        $this->assertNotSame($clock->now(), $clock->now());
    }

    public function testShouldReadInTheGivenTimezone(): void
    {
        $clock = new SystemClock(new DateTimeZone('Pacific/Auckland'));

        $this->assertSame('Pacific/Auckland', $clock->now()->getTimezone()->getName());
    }
}
