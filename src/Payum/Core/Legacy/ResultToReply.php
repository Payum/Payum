<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\Challenge;
use Payum\Core\Result\NextAction\Poll;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\Result;
use Payum\Core\Template\RendererInterface;
use function sprintf;

/**
 * Turns what a handler decided into the reply a 1.x caller is waiting to catch.
 *
 * The inverse of {@see ReplyToNextAction}. A next action 1.x had no reply for degrades to the closest
 * journey 1.x did have.
 *
 * @deprecated since 2.0, removed in 3.0 along with the replies it produces.
 */
final class ResultToReply
{
    /**
     * Null when there is nothing for a 1.x caller to act on: the operation is finished or waiting, and the
     * caller reads the outcome from the status.
     *
     * @param RendererInterface|null $renderer required to translate a {@see RenderTemplate}
     *
     * @throws LogicException when the next action has no 1.x reply
     */
    public static function translate(Result $result, ?RendererInterface $renderer = null): ?ReplyInterface
    {
        if ($result instanceof NotifyResult && $result->acknowledgement instanceof Acknowledgement) {
            return new HttpResponse(
                $result->acknowledgement->body,
                $result->acknowledgement->status,
                $result->acknowledgement->headers,
            );
        }

        $next = $result->next;

        return match (true) {
            ! $next instanceof NextAction => null,
            $next instanceof Redirect => new HttpRedirect($next->url, $next->statusCode, $next->headers),
            $next instanceof PostRedirect => new HttpPostRedirect($next->url, $next->fields),
            $next instanceof LegacyReply => $next->reply,
            // A 1.x gateway ran 3-D Secure by posting a form at the issuer. The protocol version is lost.
            $next instanceof Challenge => [] === $next->parameters
                ? new HttpRedirect($next->url)
                : new HttpPostRedirect($next->url, $next->parameters),
            // A 1.x caller that catches nothing goes to the after url, where the status reads pending.
            // retryAfterSeconds is lost.
            $next instanceof Poll => null,
            $next instanceof RenderTemplate => $renderer instanceof RendererInterface
                ? new HttpResponse($renderer->render($next->template, $next->context))
                : throw new LogicException(sprintf(
                    '%s names a template, so translating it needs a %s. Pass one, or give the gateway a caller that acts on a %s.',
                    $next::class,
                    RendererInterface::class,
                    Result::class,
                )),
            default => throw new LogicException(sprintf(
                '%s has no 1.x reply to become. The gateway needs a caller that acts on a %s.',
                $next::class,
                Result::class,
            )),
        };
    }
}
