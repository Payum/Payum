<?php
namespace Payum\Request;

use Payum\Exception\LogicException;

class BinaryMaskStatusRequest extends LogicException implements StatusRequestInterface 
{
    const STATUS_UNKNOWN = 2097152; //2^21
    
    const STATUS_FAILED = 1048576; //2^20
    
    const STATUS_SUCCESS = 32768; // 2^15

    const STATUS_IN_PROGRESS = 1024; // 2^10

    const STATUS_CANCELED = 32; //2^5; //2^1

    const STATUS_NEW = 2; //2^1
    
    /**
     * @var object
     */
    protected $model;

    /**
     * @var int
     */
    protected $status;

    /**
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $model;
        
        $this->markUnknown();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

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
    public function markInProgress()
    {
        $this->status = static::STATUS_IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function isInProgress()
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_IN_PROGRESS);
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