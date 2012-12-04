<?php
namespace Payum\Request;

use Payum\Domain\ModelInterface;
use Payum\Domain\InstructionAwareInterface;
use Payum\Exception\InvalidArgumentException;

class CreatePaymentInstructionRequest
{
    /**
     * @var \Payum\Domain\ModelInterface|\Payum\Domain\InstructionAwareInterface
     */
    protected $model;

    /**
     * @param \Payum\Domain\ModelInterface $model
     */
    public function __construct(ModelInterface $model)
    {
        if (false == $model instanceof InstructionAwareInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid domain model %s given. Should implement InstructionAwareInterface interface',
                get_class($model)
            ));
        }
        
        $this->model = $model;
    }

    /**
     * @return ModelInterface|InstructionAwareInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}