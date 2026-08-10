<?php

declare(strict_types=1);

namespace Payum\Core\Command;

use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Security\TokenInterface;

/**
 * Take the money.
 *
 * This command is **re-entrant** and that is by design, not an accident of the v1 implementation. On a
 * redirect gateway the same command is dispatched twice against the same URL:
 *
 *   1. First dispatch: no PSP state yet, so the handler opens the checkout and returns
 *      CaptureResult::pending() carrying a Redirect. The customer leaves.
 *   2. The PSP returns the customer to the capture token's own URL -- the very URL that dispatched the
 *      first one -- and the same command runs again. Now PSP state exists, so the handler finalises and
 *      returns CaptureResult::captured().
 *
 * The handler reads $context->state() and decides, exactly as
 * a v1 action reads the details array. Some gateways run one phase, some run three.
 *
 * @implements CommandInterface<CaptureResult>
 */
final class CaptureCommand implements CommandInterface
{
    /**
     * @param TokenInterface|null $token the capture token, when this came in over HTTP. Its target URL is
     *                                   what the PSP returns the customer to, and its after URL is where
     *                                   the application goes once there is nothing left to do
     * @param PaymentInterface|null $payment set directly by a headless caller that never minted a token
     * @param int|null $amount in minor units. Null captures the payment's full amount; a smaller value is
     *                         a partial capture, which requires Capability::PartialCapture
     * @param string|null $idempotencyKey passed to PSPs that accept one, so a retry cannot double-charge
     */
    public function __construct(
        public readonly ?TokenInterface $token = null,
        public readonly ?PaymentInterface $payment = null,
        public readonly ?int $amount = null,
        public readonly ?string $idempotencyKey = null,
    ) {
        if (null === $this->token && null === $this->payment) {
            throw new LogicException(sprintf(
                'A %s needs either a token or a payment: it has to know what it is capturing.',
                self::class,
            ));
        }
    }

    public static function capability(): Capability
    {
        return Capability::Capture;
    }

    public static function forPayment(PaymentInterface $payment, ?int $amount = null, ?string $idempotencyKey = null): self
    {
        return new self(payment: $payment, amount: $amount, idempotencyKey: $idempotencyKey);
    }

    public static function forToken(TokenInterface $token, ?int $amount = null, ?string $idempotencyKey = null): self
    {
        return new self(token: $token, amount: $amount, idempotencyKey: $idempotencyKey);
    }
}
