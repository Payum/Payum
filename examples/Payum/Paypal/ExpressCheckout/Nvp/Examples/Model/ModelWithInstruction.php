<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Model;

use Payum\Domain\InstructionAggregateInterface;
use Payum\Domain\InstructionAwareInterface;
use Payum\PaymentInstructionInterface;

class ModelWithInstruction implements InstructionAggregateInterface, InstructionAwareInterface
{
    protected $instruction;

    /**
     * @return \Payum\PaymentInstructionInterface
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param PaymentInstructionInterface $instruction
     *
     * @return void
     */
    public function setInstruction(PaymentInstructionInterface $instruction)
    {
        $this->instruction = $instruction;
    }
}