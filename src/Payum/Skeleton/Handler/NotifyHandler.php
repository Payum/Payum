<?php

declare(strict_types=1);

namespace Payum\Skeleton\Handler;

use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\NotImplementedException;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Result\NotifyResult;
use Psr\Http\Message\ServerRequestInterface;

final class NotifyHandler implements NotifyHandlerInterface
{
    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        throw new NotImplementedException();
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        throw new NotImplementedException();
    }
}
