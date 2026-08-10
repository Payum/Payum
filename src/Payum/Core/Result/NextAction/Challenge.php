<?php

declare(strict_types=1);

namespace Payum\Core\Result\NextAction;

use Payum\Core\Result\NextAction;

/**
 * A step-up authentication the customer must clear -- 3-D Secure being the usual one.
 *
 * Modelling this as a NextAction rather than a special case is deliberate: "requires 3DS", "requires a
 * redirect" and "scan this QR code" are the same shape -- the operation is incomplete and the customer
 * must do something. SCA stops being a per-gateway hack.
 */
final class Challenge implements NextAction
{
    /**
     * @param array<string, scalar> $parameters
     */
    public function __construct(
        public readonly string $url,
        public readonly array $parameters = [],
        /**
         * e.g. '2.2.0'. Null when the gateway does not report it.
         */
        public readonly ?string $version = null,
    ) {
    }
}
