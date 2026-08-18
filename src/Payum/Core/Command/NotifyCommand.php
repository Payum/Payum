<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Security\TokenInterface;

/**
 * A PSP has sent word that something happened.
 *
 * Unlike every other command this one may carry neither a token nor a subject. Which payment an event
 * belongs to is something verification works out, so a message arriving on an endpoint the application
 * routed itself has nothing to point at until it has been read.
 *
 * It carries no idempotency key. A key stops the same instruction reaching a PSP twice, and a notify
 * sends nothing; recognising a redelivery is de-duplication, which is a different job.
 *
 * @implements CommandInterface<NotifyResult>
 */
final class NotifyCommand implements CommandInterface
{
    private function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?SubjectInterface $subject = null,
    ) {
    }

    public static function capability(): Capability
    {
        return Capability::Webhooks;
    }

    /**
     * The application routed the endpoint itself, so there is nothing to point at.
     */
    public static function forGateway(): self
    {
        return new self();
    }

    public static function forPayment(PaymentInterface $payment): self
    {
        return new self(subject: $payment);
    }

    public static function forToken(TokenInterface $token): self
    {
        return new self(token: $token);
    }

    public function payment(): ?PaymentInterface
    {
        return $this->subject instanceof PaymentInterface ? $this->subject : null;
    }

    public function subject(): ?SubjectInterface
    {
        return $this->subject;
    }

    public function token(): ?TokenInterface
    {
        return $this->token;
    }
}
