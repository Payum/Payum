<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Handler\AuthorizeHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Result\AuthorizeResult;

/**
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class AuthorizeActionHandler extends ActionToHandlerAdapter implements AuthorizeHandlerInterface
{
    public function handle(AuthorizeCommand $command, Context $context): AuthorizeResult
    {
        /** @var AuthorizeResult */
        return $this->answer($command);
    }
}
