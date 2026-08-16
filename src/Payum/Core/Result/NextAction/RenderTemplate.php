<?php

declare(strict_types=1);

namespace Payum\Core\Result\NextAction;

use Payum\Core\Result\NextAction;

/**
 * Show the customer a page the gateway owns -- a card form, a wallet button, a "we are checking" screen.
 *
 * Carries the template name and its context, never rendered output, so the application's own renderer
 * and layout stay in charge.
 */
final class RenderTemplate implements NextAction
{
    /**
     * @param string $template a Twig template name, e.g. `@PayumAcme/checkout.html.twig`
     * @param array<string, mixed> $context
     */
    public function __construct(
        public readonly string $template,
        public readonly array $context = [],
    ) {
    }
}
