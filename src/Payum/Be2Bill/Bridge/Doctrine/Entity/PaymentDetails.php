<?php
namespace Payum\Be2Bill\Bridge\Doctrine\Entity;

use Payum\Be2Bill\Model\PaymentDetails as BasePaymentDetails;

/**
 * @deprecated since 0.6.1 will be removed in 0.7
 */
class PaymentDetails extends BasePaymentDetails
{
    protected $id;
    
    public function getId()
    {
        return $this->id;
    }
}
