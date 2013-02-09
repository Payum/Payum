<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request;

use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\PaymentInstructionAggregateInterface;

abstract class BaseInstructionRequest implements PaymentInstructionAggregateInterface 
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
    public function getPaymentInstruction()
    {
        return $this->instruction;
    }
}