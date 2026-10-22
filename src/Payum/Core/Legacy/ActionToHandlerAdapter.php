<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

use Payum\Core\Command\CommandInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Result\Result;

/**
 * Answers a command with the 1.x actions the gateway still declares, so an operation can stay an action
 * while the gateway takes commands for it. List the handler for the command, and keep the action listed
 * in {@see \Payum\Core\Gateway\DeclaresActions::actions()}:
 *
 *     public function handlers(): array { return [CaptureActionHandler::class, RefundHandler::class]; }
 *     public function actions(): array  { return [CaptureAction::class]; }
 *
 * The command becomes the 1.x request that means the same thing, as {@see LegacyGatewayAdapter} does it,
 * and what a 1.x gateway cannot report -- a transaction id, an amount, a failure -- is null on the result.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
abstract class ActionToHandlerAdapter implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param CommandInterface<Result> $command
     */
    protected function answer(CommandInterface $command): Result
    {
        return LegacyGatewayAdapter::wrap($this->gateway)->execute($command);
    }
}
