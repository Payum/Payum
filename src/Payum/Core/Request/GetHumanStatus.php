<?php
namespace Payum\Core\Request;

class GetHumanStatus extends BaseGetStatus
{
    public const STATUS_CAPTURED = 'captured';

    public const STATUS_AUTHORIZED = 'authorized';

    public const STATUS_PAYEDOUT = 'payedout';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_UNKNOWN = 'unknown';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_NEW = 'new';

    /**
     * {@inheritDoc}
     */
    public function markCaptured(): void
    {
        $this->status = static::STATUS_CAPTURED;
    }

    /**
     * {@inheritDoc}
     */
    public function isCaptured(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CAPTURED);
    }

    /**
     * {@inheritDoc}
     */
    public function markAuthorized(): void
    {
        $this->status = static::STATUS_AUTHORIZED;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthorized(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_AUTHORIZED);
    }

    /**
     * {@inheritDoc}
     */
    public function markPayedout(): void
    {
        $this->status = static::STATUS_PAYEDOUT;
    }

    /**
     * {@inheritDoc}
     */
    public function isPayedout(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PAYEDOUT);
    }
    
    /**
     * {@inheritDoc}
     */
    public function markRefunded(): void
    {
        $this->status = static::STATUS_REFUNDED;
    }

    /**
     * {@inheritDoc}
     */
    public function isRefunded(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_REFUNDED);
    }

    /**
     * {@inheritDoc}
     */
    public function markSuspended(): void
    {
        $this->status = static::STATUS_SUSPENDED;
    }

    /**
     * {@inheritDoc}
     */
    public function isSuspended(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_SUSPENDED);
    }

    /**
     * {@inheritDoc}
     */
    public function markExpired(): void
    {
        $this->status = static::STATUS_EXPIRED;
    }

    /**
     * {@inheritDoc}
     */
    public function isExpired(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_EXPIRED);
    }

    /**
     * {@inheritDoc}
     */
    public function markCanceled(): void
    {
        $this->status = static::STATUS_CANCELED;
    }

    /**
     * {@inheritDoc}
     */
    public function isCanceled(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CANCELED);
    }

    /**
     * {@inheritDoc}
     */
    public function markPending(): void
    {
        $this->status = static::STATUS_PENDING;
    }

    /**
     * {@inheritDoc}
     */
    public function isPending(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PENDING);
    }

    /**
     * {@inheritDoc}
     */
    public function markFailed(): void
    {
        $this->status = static::STATUS_FAILED;
    }

    /**
     * {@inheritDoc}
     */
    public function isFailed(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_FAILED);
    }

    /**
     * {@inheritDoc}
     */
    public function markNew(): void
    {
        $this->status = static::STATUS_NEW;
    }

    /**
     * {@inheritDoc}
     */
    public function isNew(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_NEW);
    }

    /**
     * {@inheritDoc}
     */
    public function markUnknown(): void
    {
        $this->status = static::STATUS_UNKNOWN;
    }

    /**
     * {@inheritDoc}
     */
    public function isUnknown(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_UNKNOWN);
    }

    protected function isCurrentStatusEqualTo(string $expectedStatus): bool
    {
        return $this->getValue() === $expectedStatus;
    }
}
