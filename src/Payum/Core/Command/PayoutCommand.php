<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Result\PayoutResult;
use Payum\Core\Security\TokenInterface;

/**
 * Send money out to a recipient.
 *
 * The one operation that is not about a payment: it acts on a {@see PayoutInterface}, which carries a
 * recipient rather than a customer.
 *
 * Re-entrant on the same terms as a capture. A PSP that makes a payout in one call has a handler that
 * checks whether it has already gone out and returns; one that needs approval before it executes has a
 * handler that reads which pass it is on, exactly as a redirect capture does. Neither needs its own
 * command.
 *
 * @implements CommandInterface<PayoutResult>
 */
final class PayoutCommand implements CommandInterface
{
    public function __construct(
        private readonly ?TokenInterface $token = null,
        private readonly ?PayoutInterface $payout = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (! $this->token instanceof TokenInterface && ! $this->payout instanceof PayoutInterface) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a payout: it has to know what it is paying out.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Payout;
    }

    public static function forPayout(PayoutInterface $payout, ?string $idempotencyKey = null): self
    {
        return new self(payout: $payout, idempotencyKey: $idempotencyKey);
    }

    public static function forToken(TokenInterface $token, ?string $idempotencyKey = null): self
    {
        return new self(token: $token, idempotencyKey: $idempotencyKey);
    }

    public function payout(): ?PayoutInterface
    {
        return $this->payout;
    }

    public function subject(): ?SubjectInterface
    {
        return $this->payout;
    }

    public function token(): ?TokenInterface
    {
        return $this->token;
    }
}
