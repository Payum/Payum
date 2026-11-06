<?php

declare(strict_types=1);

namespace Payum\Skeleton\Handler;

use Payum\Core\Command\CancelCommand;
use Payum\Core\Exception\NotImplementedException;
use Payum\Core\Handler\CancelHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CancelResult;

final class CancelHandler implements CancelHandlerInterface
{
    public function handle(CancelCommand $command, Context $context): CancelResult
    {
        throw new NotImplementedException();
    }
}
