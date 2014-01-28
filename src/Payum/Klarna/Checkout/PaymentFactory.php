<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Payment;

abstract class PaymentFactory
{
    /**
     * @return \Payum\Core\Payment
     */
    public static function create()
    {
        $payment = new Payment;

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}