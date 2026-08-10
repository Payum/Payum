<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * What a handler returns. Replaces v1's "mutate the request object, then throw a Reply".
 *
 * The uniform part lives here so that every result can be logged, persisted and compared the same way.
 * The parts that genuinely differ between operations -- how much was captured, whether a refund was
 * partial -- live on the subclass, one per command.
 *
 * Control flow is $next: when it is null the operation is finished, and when it is set the customer has
 * something to do first. See {@see NextAction}.
 */
abstract class Result
{
    /**
     * @param array<string, mixed> $raw the PSP's own payload, kept for the application's use
     */
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?NextAction $next = null,
        public readonly ?string $transactionId = null,
        public readonly ?Failure $failure = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * The operation is finished and did not fail.
     *
     * Note that Pending is not successful *yet* -- a bank transfer awaiting settlement is neither.
     */
    public function isSuccessful(): bool
    {
        return !$this->failure instanceof Failure && in_array(
            $this->status,
            [PaymentStatus::Authorized, PaymentStatus::Captured, PaymentStatus::Refunded, PaymentStatus::PartiallyRefunded, PaymentStatus::PaidOut],
            true,
        );
    }

    public function isFailed(): bool
    {
        return $this->failure instanceof Failure || PaymentStatus::Failed === $this->status;
    }

    /**
     * The customer must do something before this can finish. The caller should act on {@see self::$next}.
     */
    public function requiresInteraction(): bool
    {
        return $this->next instanceof NextAction;
    }
}
