<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\SyncResult;
use Payum\Core\Security\TokenInterface;

/**
 * Ask the PSP what it thinks the current state is, and record the answer.
 *
 * Only for callers: an admin screen with a refresh button, a reconciliation job, recovery after a webhook
 * that never arrived. A handler that needs fresh data mid-flow calls its api directly -- dispatching a
 * command to fetch something it uses two lines later is the indirection this model exists to remove.
 *
 * Reads rather than mutates, so it carries no idempotency key: running it twice costs a request and
 * changes nothing.
 *
 * Not every PSP offers a way to re-read, which is why Capability::Sync is a capability like any other
 * rather than something core assumes.
 *
 * @implements CommandInterface<SyncResult>
 */
final class SyncCommand implements CommandInterface
{
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?SubjectInterface $subject = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->subject instanceof SubjectInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or something to synchronise.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Sync;
    }

    public static function forPayment(PaymentInterface $payment): self
    {
        return new self(subject: $payment);
    }

    public static function forPayout(PayoutInterface $payout): self
    {
        return new self(subject: $payout);
    }

    public static function forToken(TokenInterface $token): self
    {
        return new self(token: $token);
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
