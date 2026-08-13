<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Result\NextAction;
use Payum\Core\Result\NextAction\PostRedirect;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\Result;
use function sprintf;

/**
 * Turns what a handler decided into the reply a 1.x caller is waiting to catch.
 *
 * @deprecated since 2.0, removed in 3.0 along with the replies it produces.
 */
final class ResultToReply
{
    /**
     * Null when the operation is finished and there is nothing for the caller to act on, which is the
     * same as a 1.x action returning without throwing.
     */
    public static function translate(Result $result): ?Base
    {
        $next = $result->next;

        return match (true) {
            ! $next instanceof NextAction => null,
            $next instanceof Redirect => new HttpRedirect($next->url, $next->statusCode, $next->headers),
            $next instanceof PostRedirect => new HttpPostRedirect($next->url, $next->fields),
            default => throw new LogicException(sprintf(
                '%s has no 1.x reply to become. The gateway needs a caller that acts on a %s.',
                $next::class,
                Result::class,
            )),
        };
    }
}
