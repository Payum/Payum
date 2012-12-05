<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request;

use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Domain\InstructionAggregateInterface;

abstract class BaseInstructionRequest implements InstructionAggregateInterface 
{
    /**
     * @var PaymentInstruction
     */
    protected $instruction;

    /**
     * @param PaymentInstruction $instruction
     */
    public function __construct(PaymentInstruction $instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * {@inheritdoc}
     * 
     * @return PaymentInstruction
     */
    public function getInstruction()
    {
        return $this->instruction;
    }
}