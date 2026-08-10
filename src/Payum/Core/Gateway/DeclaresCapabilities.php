<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * Optional. Implement when a gateway supports something its handler list cannot imply.
 *
 * Core already derives the operation capabilities (Capture, Refund, ...) from
 * {@see GatewayInterface::handlers()}. Declaring those again here would create a second source of truth
 * that drifts, so return only the nuance: partial refunds, multi-currency, 3-D Secure, webhooks.
 */
interface DeclaresCapabilities
{
    /**
     * @return list<Capability>
     */
    public function capabilities(): array;
}
