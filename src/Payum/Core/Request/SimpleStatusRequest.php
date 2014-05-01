<?php
namespace Payum\Core\Request;

class SimpleStatusRequest extends BaseStatusRequest
{
    const STATUS_UNKNOWN = 'unknown';
    
    const STATUS_FAILED = 'failed';

    const STATUS_SUSPENDED = 'suspended';
    
    const STATUS_EXPIRED = 'expired';
    
    const STATUS_SUCCESS = 'success';

    const STATUS_PENDING = 'pending';

    const STATUS_CANCELED = 'canceled';

    const STATUS_NEW = 'new';

    /**
     * {@inheritdoc}
     */
    public function markSuccess()
    {
        $this->status = static::STATUS_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccess()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_SUCCESS);
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
        return ($expectedStatus | $this->getStatus()) == $expectedStatus;
    }
}