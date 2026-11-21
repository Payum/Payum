<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Gateway\Capability;
use Payum\Core\Handler\WebhookEvent;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
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
        private readonly ?WebhookEvent $event = null,
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

    public static function forPayout(PayoutInterface $payout): self
    {
        return new self(subject: $payout);
    }

    public static function forSubject(SubjectInterface $subject): self
    {
        return new self(subject: $subject);
    }

    public static function forToken(TokenInterface $token): self
    {
        return new self(token: $token);
    }

    /**
     * A copy carrying an event that has already been verified, typically in the HTTP request, so that
     * handling it later (in a worker, say) does not verify again against a request that is no longer there.
     */
    public function withEvent(WebhookEvent $event): self
    {
        return new self($this->token, $this->subject, $event);
    }

    public function event(): ?WebhookEvent
    {
        return $this->event;
    }

    public function payment(): ?PaymentInterface
    {
        return $this->subject instanceof PaymentInterface ? $this->subject : null;
    }

    public function payout(): ?PayoutInterface
    {
        return $this->subject instanceof PayoutInterface ? $this->subject : null;
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
