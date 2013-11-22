<?php
namespace Payum\Be2Bill;

use Payum\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
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

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}