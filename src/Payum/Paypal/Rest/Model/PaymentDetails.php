<?php

namespace Payum\Paypal\Rest\Model;

use PayPal\Api\Payment as BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails
{
    protected $idStorage;
}
