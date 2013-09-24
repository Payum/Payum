<?php
/**
 * Created by PhpStorm.
 * User: skadabr
 * Date: 9/23/13
 * Time: 4:19 PM
 */

namespace Payum\Paypal\ExpressCheckout\Nvp\Model;

use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails as BasePaymentDetails;

class PaypalPaymentDetails extends BasePaymentDetails
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}