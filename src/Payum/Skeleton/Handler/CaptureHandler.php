<?php

declare(strict_types=1);

namespace Payum\Skeleton\Handler;

use LogicException;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        throw new LogicException('Not implemented');
    }
}
