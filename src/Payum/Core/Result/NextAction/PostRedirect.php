<?php

declare(strict_types=1);

namespace Payum\Core\Result\NextAction;

use Payum\Core\Result\NextAction;

/**
 * Send the customer to another URL by POST, normally rendered as a self-submitting form.
 *
 * The v1 equivalent is a thrown HttpPostRedirect. Note that core carries the *fields*, not rendered HTML,
 * so a headless client can submit them itself.
 */
final class PostRedirect implements NextAction
{
    /**
     * @param array<string, scalar> $fields
     */
    public function __construct(
        public readonly string $url,
        public readonly array $fields = [],
    ) {
    }
}
