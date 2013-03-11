<?php
namespace Payum\Be2Bill;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CapturePaymentInstructionAggregateAction;
use Payum\Action\StatusPaymentInstructionAggregateAction;
use Payum\Action\SyncPaymentInstructionAggregateAction;
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
        $payment->addAction(new CapturePaymentInstructionAggregateAction);
        $payment->addAction(new SyncPaymentInstructionAggregateAction);
        $payment->addAction(new StatusPaymentInstructionAggregateAction);
        
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}