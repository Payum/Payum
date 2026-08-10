<?php

declare(strict_types=1);

namespace Payum\Core\Result\NextAction;

use Payum\Core\Result\NextAction;

/**
 * Send the customer to another URL. The v1 equivalent is a thrown HttpRedirect.
 */
final class Redirect implements NextAction
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly string $url,
        public readonly int $statusCode = 302,
        public readonly array $headers = [],
    ) {
    }
}
