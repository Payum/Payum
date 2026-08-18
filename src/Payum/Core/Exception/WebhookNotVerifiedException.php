<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

/**
 * The message did not come from the PSP, or has been altered on the way.
 *
 * Its message belongs in your log rather than in the response: {@see \Payum\Core\Payum::notify()}
 * answers 400 with an empty body, because whoever failed the check is either misconfigured or probing,
 * and neither is helped by learning which check it was.
 */
class WebhookNotVerifiedException extends RuntimeException
{
}
