<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request;

class GetTransactionDetailsRequest extends BaseInstructionRequest
{
    /**
     * @var int
     */
    protected $paymentRequestN;

    /**
     * @param int $paymentRequestN
     * @param \Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction $instruction
     */
    public function __construct($paymentRequestN, Instruction $instruction)
    {
        $this->paymentRequestN = $paymentRequestN;
        
        parent::__construct($instruction);
    }

    /**
     * @return int
     */
    public function getPaymentRequestN()
    {
        return $this->paymentRequestN;
    }
}