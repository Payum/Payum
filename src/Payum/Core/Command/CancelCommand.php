<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\CancelResult;
use Payum\Core\Security\TokenInterface;

/**
 * Call it off before the money moves.
 *
 * Voids an authorization the merchant has decided not to settle, abandons a payment the customer never
 * completed, or stops a payout that has not gone out. Distinct from a refund, which gives back money that
 * has already been taken.
 *
 * Deliberately not tied to payments: cancelling is the same operation whatever it is cancelling, which is
 * why this takes a subject rather than a payment.
 *
 * Merchant-initiated rather than customer-facing, so it is usually built from the model. There is no
 * cancel token path, and a gateway needing the customer to confirm can still return a NextAction.
 *
 * @implements CommandInterface<CancelResult>
 */
final class CancelCommand implements CommandInterface
{
    /**
     * @param string|null $reason some PSPs record a reason code and surface it in their dashboard
     */
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?SubjectInterface $subject = null,
        public readonly ?string $reason = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->subject instanceof SubjectInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or something to cancel.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Cancel;
    }

    public static function forPayment(PaymentInterface $payment, ?string $reason = null): self
    {
        return new self(subject: $payment, reason: $reason);
    }

    public static function forPayout(PayoutInterface $payout, ?string $reason = null): self
    {
        return new self(subject: $payout, reason: $reason);
    }

    public static function forToken(TokenInterface $token, ?string $reason = null): self
    {
        return new self(token: $token, reason: $reason);
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
