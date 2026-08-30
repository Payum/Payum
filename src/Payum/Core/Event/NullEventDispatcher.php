<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Throws every event away, and is what the container holds until an application registers a real one.
 *
 * Having a dispatcher that always exists is what lets core emit events unconditionally: no null check at
 * any call site, and no configuration needed by an application that does not want events.
 */
final class NullEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): object
    {
        return $event;
    }
}
