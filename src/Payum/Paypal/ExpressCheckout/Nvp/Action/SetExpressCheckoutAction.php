<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\ActionInterface;
use Payum\Paypal\ExpressCheckout\Nvp;

class SetExpressCheckoutAction implements ActionInterface
{
    protected $instruction;
    
    public function __construct(ExpressCheckout\Instruction $instruction)
    {
        $this->instruction = $instruction;
    }
    
    public function getInstruction()
    {
        return $this->instruction;
    }
}