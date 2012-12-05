<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request;

use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;

class AuthorizeTokenRequest extends BaseInstructionRequest
{    
    protected $force;

    public function __construct(PaymentInstruction $instruction, $force = false)
    {
        parent::__construct($instruction);
        
        $this->force = $force;
    }
    
    public function isForced()
    {
        return $this->force;
    }
}