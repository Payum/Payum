<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Request\InstructionAggregateRequestInterface;

abstract class BaseInstructionRequest implements InstructionAggregateRequestInterface 
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction
     */
    protected $instruction;

    /**
     * @param \Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction $instruction
     */
    public function __construct(Instruction $instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction
     */
    public function getInstruction()
    {
        return $this->instruction;
    }
}