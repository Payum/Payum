<?php
namespace Payum\Be2Bill;

use Payum\Payment;
use Payum\Be2Bill\Action\CaptureAction;
use Payum\Be2Bill\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param Api $api
     * 
     * @return Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment;
        
        $payment->addApi($api);

        $payment->addAction(new CaptureAction());
        $payment->addAction(new StatusAction());
        
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}