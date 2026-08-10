<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

use Payum\Core\Middleware\MiddlewareInterface;

/**
 * Optional. Implement when a gateway needs middleware of its own.
 *
 * Most middleware is not gateway-specific — logging, locking and idempotency apply to every command
 * regardless of who handles it — and belongs on the builder instead, where it is registered once. Reach
 * for this only when the concern genuinely does not exist outside this gateway.
 */
interface DeclaresMiddleware
{
    /**
     * Container ids, resolved from the gateway's own container.
     *
     * @return list<class-string<MiddlewareInterface>>
     */
    public function middleware(): array;
}
