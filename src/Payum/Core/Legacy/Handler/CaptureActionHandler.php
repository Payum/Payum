<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Result\CaptureResult;

/**
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class CaptureActionHandler extends ActionToHandlerAdapter implements CaptureHandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        /** @var CaptureResult */
        return $this->answer($command);
    }
}
