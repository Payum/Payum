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

    public function markCaptured()
    {
        $this->status = static::STATUS_CAPTURED;
    }

    public function isCaptured()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CAPTURED);
    }

    public function markAuthorized()
    {
        $this->status = static::STATUS_AUTHORIZED;
    }

    public function isAuthorized()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_AUTHORIZED);
    }

    public function markPayedout()
    {
        $this->status = static::STATUS_PAYEDOUT;
    }

    public function isPayedout()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PAYEDOUT);
    }

    public function markRefunded()
    {
        $this->status = static::STATUS_REFUNDED;
    }

    public function isRefunded()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_REFUNDED);
    }

    public function markSuspended()
    {
        $this->status = static::STATUS_SUSPENDED;
    }

    public function isSuspended()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_SUSPENDED);
    }

    public function markExpired()
    {
        $this->status = static::STATUS_EXPIRED;
    }

    public function isExpired()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_EXPIRED);
    }

    public function markCanceled()
    {
        $this->status = static::STATUS_CANCELED;
    }

    public function isCanceled()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CANCELED);
    }

    public function markPending()
    {
        $this->status = static::STATUS_PENDING;
    }

    public function isPending()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_PENDING);
    }

    public function markFailed()
    {
        $this->status = static::STATUS_FAILED;
    }

    public function isFailed()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_FAILED);
    }

    public function markNew()
    {
        $this->status = static::STATUS_NEW;
    }

    public function isNew()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_NEW);
    }

    public function markUnknown()
    {
        $this->status = static::STATUS_UNKNOWN;
    }

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
