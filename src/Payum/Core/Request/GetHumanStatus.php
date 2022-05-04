<?php
namespace Payum\Core\Request;

class GetHumanStatus extends BaseGetStatus
{
    const STATUS_CAPTURED = 'captured';

    const STATUS_AUTHORIZED = 'authorized';

    const STATUS_PAYEDOUT = 'payedout';

    const STATUS_REFUNDED = 'refunded';

    const STATUS_UNKNOWN = 'unknown';

    const STATUS_FAILED = 'failed';

    const STATUS_SUSPENDED = 'suspended';

    const STATUS_EXPIRED = 'expired';

    const STATUS_PENDING = 'pending';

    const STATUS_CANCELED = 'canceled';

    const STATUS_NEW = 'new';

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
    public function markPayedout()
    {
        $this->status = static::STATUS_PAYEDOUT;
    }

    /**
     * {@inheritDoc}
     */
    public function isPayedout()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PAYEDOUT);
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
     * {@inheritDoc}
     */
    public function markSuspended()
    {
        $this->status = static::STATUS_SUSPENDED;
    }

    /**
     * {@inheritDoc}
     */
    public function isSuspended()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_SUSPENDED);
    }

    /**
     * {@inheritDoc}
     */
    public function markExpired()
    {
        $this->status = static::STATUS_EXPIRED;
    }

    /**
     * {@inheritDoc}
     */
    public function isExpired()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_EXPIRED);
    }

    /**
     * {@inheritDoc}
     */
    public function markCanceled()
    {
        $this->status = static::STATUS_CANCELED;
    }

    /**
     * {@inheritDoc}
     */
    public function isCanceled()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CANCELED);
    }

    /**
     * {@inheritDoc}
     */
    public function markPending()
    {
        $this->status = static::STATUS_PENDING;
    }

    /**
     * {@inheritDoc}
     */
    public function isPending()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PENDING);
    }

    /**
     * {@inheritDoc}
     */
    public function markFailed()
    {
        $this->status = static::STATUS_FAILED;
    }

    /**
     * {@inheritDoc}
     */
    public function isFailed()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_FAILED);
    }

    /**
     * {@inheritDoc}
     */
    public function markNew()
    {
        $this->status = static::STATUS_NEW;
    }

    /**
     * {@inheritDoc}
     */
    public function isNew()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_NEW);
    }

    /**
     * {@inheritDoc}
     */
    public function markUnknown()
    {
        $this->status = static::STATUS_UNKNOWN;
    }

    /**
     * {@inheritDoc}
     */
    public function isUnknown()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_UNKNOWN);
    }

    /**
     * @param string $expectedStatus
     *
     * @return boolean
     */
    protected function isCurrentStatusEqualTo($expectedStatus)
    {
        return $this->getValue() === $expectedStatus;
    }
}
