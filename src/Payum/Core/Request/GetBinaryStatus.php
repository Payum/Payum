<?php
namespace Payum\Core\Request;

class GetBinaryStatus extends BaseGetStatus
{
    const STATUS_PAYEDOUT = 4194304; //2^22

    const STATUS_UNKNOWN = 2097152; //2^21

    const STATUS_FAILED = 1048576; //2^20

    const STATUS_SUSPENDED = 524288; // 2^19

    const STATUS_EXPIRED = 262144; // 2^18

    const STATUS_PENDING = 1024; // 2^10

    const STATUS_CANCELED = 32; //2^5

    const STATUS_REFUNDED = 16; // 2^4

    const STATUS_AUTHORIZED = 8; // 2^3

    const STATUS_CAPTURED = 4; // 2^2

    const STATUS_NEW = 2; //2^1

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

    protected function isCurrentStatusEqualTo(int $expectedStatus): bool
    {
        return ($expectedStatus | $this->getValue()) == $expectedStatus;
    }
}
