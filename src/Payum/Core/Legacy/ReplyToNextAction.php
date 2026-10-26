<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;

/**
 * Turns the reply a 1.x action threw into what a 2.0 caller acts on. The inverse of {@see ResultToReply}.
 *
 * Total by construction: a reply core has no next action for becomes a {@see LegacyReply} carrying it, so
 * a gateway that still throws loses nothing on the way across, including replies of its own that core
 * will never know about.
 *
 * @deprecated since 2.0, removed in 3.0 along with the replies it translates.
 */
final class ReplyToNextAction
{
    /**
     * Null when nothing was thrown, which is a 1.x action saying it is finished.
     */
    public static function translate(?ReplyInterface $reply): ?NextAction
    {
        // HttpRedirect and HttpPostRedirect both extend HttpResponse, so the narrow cases come first or
        // every redirect is handed over as a reply nobody can act on.
        if ($reply instanceof HttpRedirect) {
            /** @var array<string, string> $headers */
            $headers = $reply->getHeaders();

            // The reply writes Location from the url. Carrying it would say the same thing twice, and the
            // reply built back from this next action writes it again anyway.
            unset($headers['Location']);

            return new Redirect($reply->getUrl(), $reply->getStatusCode(), $headers);
        }

        // The status code and headers of the page carrying the form are presentation, and a next action
        // carries intent. A gateway that needs a particular response builds it from the fields itself.
        if ($reply instanceof HttpPostRedirect) {
            /** @var array<string, scalar> $fields */
            $fields = $reply->getFields();

            return new PostRedirect($reply->getUrl(), $fields);
        }

        return $reply instanceof ReplyInterface ? new LegacyReply($reply) : null;
    }

    /**
     * The answer a 1.x notify action gave the PSP, as the value a notify handler returns.
     *
     * A webhook is answered rather than acted on, which is why this is separate: an
     * {@see Acknowledgement} is not something the customer must do, so it is not a {@see NextAction}.
     * Null when the action threw nothing, or threw something that is not an answer.
     */
    public static function acknowledgement(?ReplyInterface $reply): ?Acknowledgement
    {
        if (! $reply instanceof HttpResponse) {
            return null;
        }

        /** @var array<string, string> $headers */
        $headers = $reply->getHeaders();

        return new Acknowledgement($reply->getStatusCode(), $reply->getContent(), $headers);
    }
}
