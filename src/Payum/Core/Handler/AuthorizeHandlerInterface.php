<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Result\AuthorizeResult;

/**
 * Holds the money without taking it.
 */
interface AuthorizeHandlerInterface extends HandlerInterface
{
    public function handle(AuthorizeCommand $command, Context $context): AuthorizeResult;
}
