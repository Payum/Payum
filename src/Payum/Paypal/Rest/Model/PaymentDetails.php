<?php
/**
 * Created by PhpStorm.
 * User: skadabr
 * Date: 9/25/13
 * Time: 4:28 PM
 */
namespace Payum\Paypal\Rest\Model;

use PayPal\Api\Payment as BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails
{
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}