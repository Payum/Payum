<?php

declare(strict_types=1);

namespace Payum\Core\Result\NextAction;

use Payum\Core\Result\NextAction;

/**
 * Nothing to show the customer; the PSP has not settled yet. Ask again later.
 *
 * Typical of bank transfers and offline methods, where the outcome arrives by webhook minutes or days
 * later. The application decides whether to poll or simply wait for the notification.
 */
final class Poll implements NextAction
{
    public function __construct(
        public readonly ?int $retryAfterSeconds = null,
    ) {
    }
}
