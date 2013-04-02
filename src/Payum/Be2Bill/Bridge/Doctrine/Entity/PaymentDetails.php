<?php
namespace Payum\Be2Bill\Bridge\Doctrine\Entity;

use Payum\Be2Bill\Model\PaymentDetails as BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails
{
    protected $id;
    
    public function getId()
    {
        return $this->id;
    }
}
