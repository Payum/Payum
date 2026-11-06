<?php

declare(strict_types=1);

namespace Payum\Skeleton\Handler;

use LogicException;
use Payum\Core\Command\NotifyCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Result\NotifyResult;
use Psr\Http\Message\ServerRequestInterface;

final class NotifyHandler implements NotifyHandlerInterface
{
    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        throw new LogicException('Not implemented');
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        throw new LogicException('Not implemented');
    }
}
