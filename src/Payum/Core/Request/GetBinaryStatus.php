<?php

namespace Payum\Core\Request;

class GetBinaryStatus extends BaseGetStatus
{
    public const STATUS_PAYEDOUT = 4_194_304; //2^22

    public const STATUS_UNKNOWN = 2_097_152; //2^21

    public const STATUS_FAILED = 1_048_576; //2^20

    public const STATUS_SUSPENDED = 524288; // 2^19

    public const STATUS_EXPIRED = 262144; // 2^18

    public const STATUS_PENDING = 1024; // 2^10

    public const STATUS_CANCELED = 32; //2^5

    public const STATUS_REFUNDED = 16; // 2^4

    public const STATUS_AUTHORIZED = 8; // 2^3

    public const STATUS_CAPTURED = 4; // 2^2

    public const STATUS_NEW = 2; //2^1

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
     * @param int $expectedStatus
     *
     * @return boolean
     */
    protected function isCurrentStatusEqualTo($expectedStatus)
    {
        return ($expectedStatus | $this->getValue()) == $expectedStatus;
    }
}
