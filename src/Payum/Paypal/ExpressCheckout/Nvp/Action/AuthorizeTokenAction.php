<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\ActionInterface;
use Payum\Paypal\ExpressCheckout\Instruction;

class AuthorizeTokenAction implements ActionInterface
{
    protected $instruction;
    
    protected $force;

    public function __construct(Instruction $instruction, $force = false)
    {
        $this->instruction = $instruction;
        $this->force = $force;
    }

    public function getInstruction()
    {
        return $this->instruction;
    }
    
    public function isForced()
    {
        return $this->force;
    }
}