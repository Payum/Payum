<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\NotifyCommand;
use Payum\Core\Exception\WebhookNotVerifiedException;
use Payum\Core\Result\NotifyResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Receives what a PSP sends when something happens to a payment.
 *
 * Checking that a message is genuine and acting on what it says are separate methods. Verification is
 * cheap, security-critical and has to answer immediately; handling is the slow part. A gateway whose PSP
 * signs nothing says so with {@see WebhookEvent::unverified()} rather than by leaving the check out.
 */
interface NotifyHandlerInterface extends HandlerInterface
{
    /**
     * Establish that the message came from the PSP.
     *
     * Read the signed bytes with `(string) $request->getBody()`, which seeks to the start of the stream
     * first. Do not reach for the payment here: verification that depends on stored state is handling in
     * disguise, and this method is the one that has to stay cheap.
     *
     * @throws WebhookNotVerifiedException when the message is not genuine
     */
    public function verify(ServerRequestInterface $request): WebhookEvent;

    public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult;
}
