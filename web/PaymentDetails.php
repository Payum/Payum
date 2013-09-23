<?php

use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails as BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}