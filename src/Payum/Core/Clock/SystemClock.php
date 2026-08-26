<?php

declare(strict_types=1);

namespace Payum\Core\Clock;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

/**
 * The machine's own time, registered as {@see ClockInterface} in the global container.
 *
 * Replace it there -- with `PayumBuilder::addGlobalService(ClockInterface::class, $clock)`, or from the
 * application's own container -- and every time Payum reads follows the replacement.
 */
final class SystemClock implements ClockInterface
{
    /**
     * @param DateTimeZone|null $timezone null reads in PHP's default timezone
     */
    public function __construct(
        private readonly ?DateTimeZone $timezone = null
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}
