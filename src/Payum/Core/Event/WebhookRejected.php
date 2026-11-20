<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Exception\WebhookNotVerifiedException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * A message that claimed to be from a PSP failed verification.
 *
 * Dispatched by {@see \Payum\Core\Gateway::verifyWebhook()}, which runs before any command exists, so
 * unlike the other events this one has neither a command nor a context.
 */
final class WebhookRejected
{
    public function __construct(
        public readonly string $gatewayName,
        public readonly ServerRequestInterface $request,
        public readonly WebhookNotVerifiedException $exception,
    ) {
    }
}
