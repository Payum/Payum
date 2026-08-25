<?php

declare(strict_types=1);

namespace Payum\Core\Legacy\Handler;

use Payum\Core\Command\NotifyCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\NotifyHandlerInterface;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Legacy\ActionToHandlerAdapter;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Result\Acknowledgement;
use Payum\Core\Result\NotifyResult;
use Psr\Http\Message\ServerRequestInterface;
use function is_array;

/**
 * A 1.x notify action, answering a {@see NotifyCommand}.
 *
 * The one adapter that cannot honour the interface it implements. 2.0 splits deciding a message is genuine
 * from acting on it, and a 1.x action does both inside execute(); there is no seam to pull them apart on.
 * So verification is reported as {@see WebhookEvent::unverified()} and the action goes on checking the
 * message itself, exactly as it did before -- which is no weaker than 1.x was, and no stronger.
 *
 * @deprecated since 2.0, removed in 3.0 along with actions.
 */
final class NotifyActionHandler extends ActionToHandlerAdapter implements NotifyHandlerInterface
{
    public function verify(ServerRequestInterface $request): WebhookEvent
    {
        $body = $request->getParsedBody();

        return WebhookEvent::unverified(is_array($body) ? $body : []);
    }

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
    {
        $reply = $this->dispatch(Notify::class, $context);

        // A 1.x action answers the PSP by throwing the response it wants sent back.
        if ($reply instanceof Base && ! $reply instanceof HttpResponse) {
            throw $reply;
        }

        /** @var array<string, string> $headers */
        $headers = $reply instanceof HttpResponse ? $reply->getHeaders() : [];

        return NotifyResult::handled(
            $this->status($context),
            $reply instanceof HttpResponse
                ? new Acknowledgement($reply->getStatusCode(), $reply->getContent(), $headers)
                : null,
        );
    }
}
