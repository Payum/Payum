<?php

declare(strict_types=1);

namespace Payum\Core\Event;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Result\Result;

/**
 * A PSP sent a message and the gateway decided what to make of it.
 *
 * Dispatched after {@see \Payum\Core\Handler\NotifyHandlerInterface::verify()} and before the handler
 * acts on it, so a message that fails verification never reaches a listener -- that one throws, and
 * surfaces as {@see CommandFailed}.
 *
 * Check {@see WebhookEvent::isVerified()} before believing what the payload says. An unverified message
 * is one the PSP offers no way to check, so its own id is the only thing worth recording, and that only
 * to recognise a redelivery.
 */
final class WebhookReceived extends Event
{
    /**
     * @param CommandInterface<Result> $command
     */
    public function __construct(
        CommandInterface $command,
        Context $context,
        public readonly WebhookEvent $webhook,
    ) {
        parent::__construct($command, $context);
    }
}
