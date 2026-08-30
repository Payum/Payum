<?php

declare(strict_types=1);

namespace Payum\Core\Event;

/**
 * A command is about to run.
 *
 * Dispatched outside the middleware pipeline, so nothing has had a chance to see the command yet. The
 * subject and the token on the context are already resolved, which is what makes this the place to
 * record "someone asked to capture payment 42".
 */
final class CommandDispatched extends Event
{
}
