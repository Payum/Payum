<?php
namespace Payum\Klarna\Invoice;

use Payum\Core\Payment;

abstract class PaymentFactory
{
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
