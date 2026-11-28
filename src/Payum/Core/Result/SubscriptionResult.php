<?php

declare(strict_types=1);

namespace Payum\Core\Result;

/**
 * The outcome of a create, cancel or sync of a gateway-scheduled subscription. It concludes nothing about a
 * payment, so its status is always null; the subscription's own state is in {@see self::subscription()}.
 */
final class SubscriptionResult extends Result
{
    private ?SubscriptionDetails $subscription = null;

    private ?Renewal $renewal = null;

    /**
     * @param array<string, mixed> $raw
     */
    public static function active(SubscriptionDetails $details, array $raw = []): self
    {
        return self::withDetails($details, $raw);
    }

    /**
     * The customer has something to do first, such as approve the subscription at the PSP.
     *
     * @param array<string, mixed> $raw
     */
    public static function pending(NextAction $next, array $raw = []): self
    {
        return new self(null, $next, raw: $raw);
    }

    /**
     * What the PSP reports after a cancel or a sync.
     *
     * @param array<string, mixed> $raw
     */
    public static function updated(SubscriptionDetails $details, array $raw = []): self
    {
        return self::withDetails($details, $raw);
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function failed(Failure $failure, array $raw = []): self
    {
        return new self(null, failure: $failure, raw: $raw);
    }

    public function subscription(): ?SubscriptionDetails
    {
        return $this->subscription;
    }

    /**
     * The first charge, when the PSP took one while creating the subscription.
     */
    public function renewal(): ?Renewal
    {
        return $this->renewal;
    }

    public function withRenewal(Renewal $renewal): self
    {
        $result = clone $this;
        $result->renewal = $renewal;

        return $result;
    }

    /**
     * @param array<string, mixed> $raw
     */
    private static function withDetails(SubscriptionDetails $details, array $raw): self
    {
        $result = new self(null, raw: $raw);
        $result->subscription = $details;

        return $result;
    }
}
