<?php

declare(strict_types=1);

namespace Payum\Skeleton\Handler;

use LogicException;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\RefundHandlerInterface;
use Payum\Core\Result\RefundResult;

final class RefundHandler implements RefundHandlerInterface
{
    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        throw new LogicException('Not implemented');
    }
}
