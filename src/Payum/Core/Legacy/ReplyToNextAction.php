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
 * A reply with no next action of its own, including one a gateway defines, is carried as a {@see LegacyReply}.
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

            // HttpRedirect derives Location from the url and writes it again on the way back.
            unset($headers['Location']);

            return new Redirect($reply->getUrl(), $reply->getStatusCode(), $headers);
        }

        // The status code and headers of the page carrying the form are not carried across.
        if ($reply instanceof HttpPostRedirect) {
            /** @var array<string, scalar> $fields */
            $fields = $reply->getFields();

            return new PostRedirect($reply->getUrl(), $fields);
        }

        return $reply instanceof ReplyInterface ? new LegacyReply($reply) : null;
    }

    /**
     * The response a 1.x notify action threw at the PSP, as the acknowledgement a notify handler returns.
     * Null when the action threw nothing, or threw something other than a response.
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
