<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\PayoutCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\PayoutHandlerInterface;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Request\Payout;
use Payum\Core\Result\PayoutResult;

/**
 * A 1.x payout action, answering a {@see PayoutCommand}.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class PayoutActionHandler extends ActionToHandlerAdapter implements PayoutHandlerInterface
{
    public function handle(PayoutCommand $command, Context $context): PayoutResult
    {
        [$status, $next] = $this->run(Payout::class, $context);

        return new PayoutResult($status, $next);
    }
}
