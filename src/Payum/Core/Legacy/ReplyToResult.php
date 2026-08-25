<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;

/**
 * Turns the reply a 1.x action threw into the next action that means the same thing.
 *
 * The inverse of {@see ResultToReply}, and deliberately not its mirror image: a reply is a rendered HTTP
 * response and a {@see NextAction} is intent, so only the replies that carry intent translate. Everything
 * else answers null, and the caller rethrows -- which leaves a 1.x caller catching exactly what it caught
 * before.
 *
 * @deprecated since 2.0, removed in 3.0 along with the replies it translates.
 */
final class ReplyToResult
{
    public static function translate(ReplyInterface $reply): ?NextAction
    {
        /** @var array<string, scalar> $fields */
        $fields = $reply instanceof HttpPostRedirect ? $reply->getFields() : [];

        return match (true) {
            // Both of these extend HttpResponse, so the specific has to be asked before the general.
            $reply instanceof HttpPostRedirect => new PostRedirect($reply->getUrl(), $fields),
            $reply instanceof HttpRedirect => new Redirect($reply->getUrl(), $reply->getStatusCode(), $reply->getHeaders()),
            default => null,
        };
    }
}
