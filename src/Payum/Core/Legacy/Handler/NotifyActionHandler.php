<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\NotifyCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Result\NotifyResult;
use Psr\Http\Message\ServerRequestInterface;
use function is_array;

/**
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class NotifyActionHandler extends ActionToHandlerAdapter implements NotifyHandlerInterface
{
    /**
     * A 1.x notify action checks the message itself, when it handles it.
     */
    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        $body = $request->getParsedBody();

        return WebhookEvent::unverified(is_array($body) ? $body : []);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        /** @var NotifyResult */
        return $this->answer($command);
    }
}
