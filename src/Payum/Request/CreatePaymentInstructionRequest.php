<?php
namespace Payum\Request;

use Payum\Domain\ModelInterface;
use Payum\Domain\InstructionAggregateInterface;
use Payum\Exception\InvalidArgumentException;

class CreatePaymentInstructionRequest
{
    /**
     * @var \Payum\Domain\ModelInterface|InstructionAggregateInterface
     */
    protected $model;

    /**
     * @param \Payum\Domain\ModelInterface $model
     */
    public function __construct(ModelInterface $model)
    {
        if (false == $model instanceof InstructionAggregateInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid domain model %s given. Should implement InstructionAggregateInterface interface',
                get_class($model)
            ));
        }
        
        $this->model = $model;
    }

    /**
     * @return ModelInterface|InstructionAggregateInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}