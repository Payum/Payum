<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

/**
 * Optional. Implement to say where a middleware belongs in the pipeline without the registering code
 * having to know.
 *
 * Higher runs further out: first on the way in, last on the way back. Middleware that does not implement
 * this sits at 0, and registration order breaks ties.
 */
interface HasPriority
{
    public static function priority(): int;
}
