<?php

declare(strict_types=1);

namespace Payum\Core\Event;

/**
 * A command is about to run.
 *
 * Dispatched outside the middleware pipeline, so nothing has had a chance to see the command yet. The
 * token on the context is resolved and the subject loads on first use, which is what makes this the place
 * to record "someone asked to capture payment 42".
 */
final class CommandDispatched extends Event
{
}
