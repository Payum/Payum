<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Result\CaptureResult;

/**
 * Takes the money.
 *
 * Implementations get their API, config and any core services by constructor injection. Nothing about the
 * gateway is passed in through handle() -- the Context carries only what is specific to this one
 * execution: the payment, the token, the inbound HTTP request and the PSP state.
 *
 * Bear in mind this may be called more than once for a single payment. See {@see CaptureCommand}.
 */
interface CaptureHandlerInterface extends HandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult;
}
