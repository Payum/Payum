<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a {@see \Payum\Core\Command\NotifyCommand}.
 *
 * Carries an {@see Acknowledgement} on top of the usual outcome, because a webhook is the one operation
 * whose caller is the PSP rather than a customer.
 */
final class NotifyResult extends Result
{
    private ?SubscriptionDetails $subscription = null;

    private ?Renewal $renewal = null;

    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        ?PaymentStatus $status,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
        public readonly ?Acknowledgement $acknowledgement = null,
        private readonly bool $retry = false,
    ) {
        parent::__construct($status, null, $transactionId, $failure, $raw);
    }

    /**
     * The event was read and acted on.
     *
     * A null status concludes nothing about the payment, which is right for an event that reports
     * something the payment's state does not describe.
     *
     * @param array<string, mixed> $raw
     */
    public static function handled(
        ?PaymentStatus $status = null,
        ?Acknowledgement $acknowledgement = null,
        ?string $transactionId = null,
        ?Failure $failure = null,
        array $raw = [],
    ): self {
        return new self($status, transactionId: $transactionId, failure: $failure, raw: $raw, acknowledgement: $acknowledgement);
    }

    /**
     * An event type this gateway has no interest in.
     *
     * The payment is left alone and the PSP is still answered successfully. Rejecting a message because
     * it was not recognised makes the PSP redeliver it on a backoff schedule for as long as it keeps
     * failing.
     */
    public static function ignored(?Acknowledgement $acknowledgement = null): self
    {
        return new self(null, acknowledgement: $acknowledgement);
    }

    /**
     * An event that needs a subject arrived before one could be found, so the PSP is answered with an
     * error and delivers it again later. Answers 503 unless told otherwise.
     */
    public static function retry(?Acknowledgement $acknowledgement = null): self
    {
        return new self(null, acknowledgement: $acknowledgement ?? new Acknowledgement(503), retry: true);
    }

    public function isRetry(): bool
    {
        return $this->retry;
    }

    /**
     * What the event reports about the subscription it names, for core to record.
     */
    public function subscription(): ?SubscriptionDetails
    {
        return $this->subscription;
    }

    /**
     * The charge the PSP made on the subscription the event names, for core to record.
     */
    public function renewal(): ?Renewal
    {
        return $this->renewal;
    }

    public function withSubscription(SubscriptionDetails $details): self
    {
        $result = clone $this;
        $result->subscription = $details;

        return $result;
    }

    public function withRenewal(Renewal $renewal): self
    {
        $result = clone $this;
        $result->renewal = $renewal;

        return $result;
    }
}
