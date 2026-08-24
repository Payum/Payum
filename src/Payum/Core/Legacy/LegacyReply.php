<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\RenderTemplate;

/**
 * A 1.x reply 2.0 has no vocabulary for, handed over intact.
 *
 * Nearly always an {@see HttpResponse} carrying markup a 1.x action rendered for itself -- a card form, a
 * wallet button. {@see RenderTemplate} names a template and its context and leaves rendering to the
 * application, and there is no way back from rendered HTML to the template that produced it.
 *
 * A caller that gets one has to send the reply itself:
 *
 *     if ($result->next instanceof LegacyReply && $result->next->reply instanceof HttpResponse) {
 *         return new Response($result->next->reply->getContent(), ...);
 *     }
 *
 * @deprecated since 2.0, removed in 3.0 along with replies.
 */
final class LegacyReply implements NextAction
{
    public function __construct(
        public readonly ReplyInterface $reply,
    ) {
    }
}
