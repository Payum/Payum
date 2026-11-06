<?php

declare(strict_types=1);

namespace Payum\Skeleton\Handler;

use LogicException;
use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Handler\AuthorizeHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\AuthorizeResult;

final class AuthorizeHandler implements AuthorizeHandlerInterface
{
    public function handle(AuthorizeCommand $command, Context $context): AuthorizeResult
    {
        throw new LogicException('Not implemented');
    }
}
