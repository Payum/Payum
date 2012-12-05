<?php
namespace Payum\Be2Bill\Bridge\Doctrine\Entity;

use Payum\Be2Bill\PaymentInstruction as BasePaymentInstruction;

class PaymentInstruction extends BasePaymentInstruction
{
    protected $id;
    
    public function getId()
    {
        return $this->id;
    }
}
