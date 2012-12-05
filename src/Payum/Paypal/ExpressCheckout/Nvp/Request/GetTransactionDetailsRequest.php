<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request;

use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;

class GetTransactionDetailsRequest extends BaseInstructionRequest
{
    /**
     * @var int
     */
    protected $paymentRequestN;

    /**
     * @param int $paymentRequestN
     * @param PaymentInstruction $instruction
     */
    public function __construct($paymentRequestN, PaymentInstruction $instruction)
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