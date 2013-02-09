<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Model;

use Payum\Domain\InstructionAggregateInterface;
use Payum\Domain\InstructionAwareInterface;

class ModelWithInstruction implements InstructionAggregateInterface, InstructionAwareInterface
{
    /**
     * @var object
     */
    protected $instruction;

    /**
     * {@inheritdoc}
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * {@inheritdoc}
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }
}