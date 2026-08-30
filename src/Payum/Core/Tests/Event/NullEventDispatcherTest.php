<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Event;

use Payum\Core\Event\NullEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;

final class NullEventDispatcherTest extends TestCase
{
    public function testShouldImplementThePsr14Dispatcher(): void
    {
        $this->assertInstanceOf(EventDispatcherInterface::class, new NullEventDispatcher());
    }

    public function testShouldGiveBackTheVeryEventItWasGiven(): void
    {
        $event = new stdClass();

        $this->assertSame($event, (new NullEventDispatcher())->dispatch($event));
    }
}
