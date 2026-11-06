<?php

declare(strict_types=1);

namespace Payum\Offline\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Payum\Offline\Constants;

/**
 * Keeps the `status` detail in step with every result, so the status action, the sync and refund handlers and
 * applications reading the details agree even when an application replaces a handler.
 */
final class StatusToDetailsMiddleware implements MiddlewareInterface
{
    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $result = $next($command, $context);

        if ($result->status instanceof PaymentStatus && $context->subject() instanceof SubjectInterface) {
            $context->state()[Constants::FIELD_STATUS] = $result->status->value;
        }

        return $result;
    }
}
