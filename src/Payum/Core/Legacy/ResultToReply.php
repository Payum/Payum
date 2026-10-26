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
 * The inverse of {@see ReplyToNextAction}. Every next action core ships translates: the two that 1.x
 * never had a reply for degrade to the closest journey it did have, rather than leaving a caller to
 * discover the gap at runtime.
 *
 * @deprecated since 2.0, removed in 3.0 along with the replies it produces.
 */
final class ResultToReply
{
    /**
     * Null when there is nothing for a 1.x caller to act on, which is the same as a 1.x action returning
     * without throwing: the operation is finished, or it is waiting, and either way the caller reads the
     * outcome from the status.
     *
     * @param RendererInterface|null $renderer resolves a {@see RenderTemplate}, which names a template
     *                                         rather than carrying markup. Without one, a result naming
     *                                         a template has no reply to become
     *
     * @throws LogicException when the result names a next action that has no 1.x journey to degrade to
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
            // A reply the gateway threw in the first place goes back as the object it was.
            $next instanceof LegacyReply => $next->reply,
            // 1.x had no name for a step-up, but it ran the journey: post the parameters at the issuer's
            // page and let it come back to the return url the gateway put among them. The protocol
            // version has nowhere to go, and nothing in 1.x reads it.
            $next instanceof Challenge => [] === $next->parameters
                ? new HttpRedirect($next->url)
                : new HttpPostRedirect($next->url, $next->parameters),
            // Nothing to show the customer. A 1.x caller reads "nothing thrown" as "go to the after
            // url", where the recorded status says pending -- which is what the poll is waiting for.
            // retryAfterSeconds is lost; a caller that wants it acts on the Result.
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
