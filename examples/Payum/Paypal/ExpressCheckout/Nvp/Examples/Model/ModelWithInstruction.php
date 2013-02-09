<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Model;

use Payum\PaymentInstructionAggregateInterface;
use Payum\PaymentInstructionAwareInterface;

class ModelWithInstruction implements PaymentInstructionAggregateInterface, PaymentInstructionAwareInterface
{
    /**
     * @var object
     */
    protected $instruction;

    /**
     * {@inheritdoc}
     */
    public function getPaymentInstruction()
    {
        return $this->instruction;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentInstruction($instruction)
    {
        $this->instruction = $instruction;
    }
}