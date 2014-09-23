<?php
namespace Payum\Core\Request;

class GetBinaryStatus extends BaseGetStatus
{
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
    public function markCaptured()
    {
        $this->status = static::STATUS_CAPTURED;
    }

    /**
     * {@inheritDoc}
     */
    public function isCaptured()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CAPTURED);
    }

    /**
     * {@inheritDoc}
     */
    public function markAuthorized()
    {
        $this->status = static::STATUS_AUTHORIZED;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthorized()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_AUTHORIZED);
    }

    /**
     * {@inheritDoc}
     */
    public function markRefunded()
    {
        $this->status = static::STATUS_REFUNDED;
    }

    /**
     * {@inheritDoc}
     */
    public function isRefunded()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_REFUNDED);
    }

    /**
     * {@inheritdoc}
     */
    public function markSuspended()
    {
        $this->status = static::STATUS_SUSPENDED;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuspended()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_SUSPENDED);
    }

    /**
     * {@inheritdoc}
     */
    public function markExpired()
    {
        $this->status = static::STATUS_EXPIRED;
    }

    /**
     * {@inheritdoc}
     */
    public function isExpired()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_EXPIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function markCanceled()
    {
        $this->status = static::STATUS_CANCELED;
    }

    /**
     * {@inheritdoc}
     */
    public function isCanceled()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CANCELED);
    }

    /**
     * {@inheritdoc}
     */
    public function markPending()
    {
        $this->status = static::STATUS_PENDING;
    }

    /**
     * {@inheritdoc}
     */
    public function isPending()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PENDING);
    }

    /**
     * {@inheritdoc}
     */
    public function markFailed()
    {
        $this->status = static::STATUS_FAILED;
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_FAILED);
    }

    /**
     * {@inheritdoc}
     */
    public function markNew()
    {
        $this->status = static::STATUS_NEW;
    }

    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_NEW);
    }

    /**
     * {@inheritdoc}
     */
    public function markUnknown()
    {
        $this->status = static::STATUS_UNKNOWN;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnknown()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_UNKNOWN);
    }

    /**
     * @param int $expectedStatus
     * 
     * @return boolean
     */
    protected function isCurrentStatusEqualTo($expectedStatus)
    {
        return ($expectedStatus | $this->getValue()) == $expectedStatus;
    }
}