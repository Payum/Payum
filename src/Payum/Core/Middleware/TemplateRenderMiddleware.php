<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\NextAction\RenderTemplate;
use Payum\Core\Result\Result;
use function array_merge;

/**
 * Fills in the variables every template gets, so a handler naming a template does not have to pass them.
 */
final class TemplateRenderMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * Outermost, so every RenderTemplate leaving the pipeline is filled in, whichever middleware or
     * handler produced it.
     */
    public static function priority(): int
    {
        return 2000;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        /** @var Result $result */
        $result = $next($command, $context);

        if ($result->next instanceof RenderTemplate) {
            $result->next->context = array_merge(
                [
                    'context' => $context,
                    'gateway' => $context->gateway(),
                    'command' => $command,
                    'subject' => $context->subject(),
                    'token' => $context->token(),
                ],
                $result->next->context
            );
        }

        return $result;
    }
}
