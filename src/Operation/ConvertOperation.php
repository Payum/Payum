<?php
namespace Paymnt\Operation;

class ConvertOperation implements OperationInterface
{
    protected $sourceOperation;

    protected $targetOperation;

    public function __construct(OperationInterface $sourceOperation)
    {
        $this->sourceOperation = $sourceOperation;
    }

    public function getSourceOperation()
    {
        return $this->sourceOperation;
    }

    public function getTargetOperation()
    {
        return $this->targetOperation;
    }

    public function setTargetOperation(OperationInterface $targetOperation)
    {
        $this->targetOperation = $targetOperation;
    }
}