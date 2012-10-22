<?php
namespace Payum\Request;

use Payum\Exception\LogicException;

class BinaryMaskStatusRequest extends LogicException implements StatusRequestInterface 
{
    /**
     * @var mixed
     */
    protected $request;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var array
     */
    protected $statusesMap = array(
        'unknown' => 2097152, //2^21
        'failed' => 1048576, //2^20
        'success' => 32768, // 2^15 
        'in_progress' => 1024, // 2^10
        'canceled' => 32, //2^5
        'new' => 2, //2^1
    );

    /**
     * @param mixed $request
     */
    public function __construct($request)
    {
        $this->request = $request;
        
        $this->markUnknown();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return $this->request;
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
        $this->status = $this->statusesMap['success'];
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccess()
    {
        return $this->isCurrentStatusEqualTo($this->statusesMap['success']);
    }

    /**
     * {@inheritdoc}
     */
    public function markCanceled()
    {
        $this->status = $this->statusesMap['canceled'];
    }

    /**
     * {@inheritdoc}
     */
    public function isCanceled()
    {
        return $this->isCurrentStatusEqualTo($this->statusesMap['canceled']);
    }

    /**
     * {@inheritdoc}
     */
    public function markInProgress()
    {
        $this->status = $this->statusesMap['in_progress'];
    }

    /**
     * {@inheritdoc}
     */
    public function isInProgress()
    {
        return $this->isCurrentStatusEqualTo($this->statusesMap['in_progress']);
    }

    /**
     * {@inheritdoc}
     */
    public function markFailed()
    {
        $this->status = $this->statusesMap['failed'];
    }

    /**
     * {@inheritdoc}
     */
    public function isFailed()
    {
        return $this->isCurrentStatusEqualTo($this->statusesMap['failed']);
    }

    /**
     * {@inheritdoc}
     */
    public function markNew()
    {
        $this->status = $this->statusesMap['new'];
    }

    /**
     * {@inheritdoc}
     */
    public function isNew()
    {
        return $this->isCurrentStatusEqualTo($this->statusesMap['new']);
    }

    /**
     * {@inheritdoc}
     */
    function markUnknown()
    {
        $this->status = $this->statusesMap['unknown'];
    }

    /**
     * {@inheritdoc}
     */
    function isUnknown()
    {
        return $this->isCurrentStatusEqualTo($this->statusesMap['unknown']);
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