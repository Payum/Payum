<?php
namespace Payum\Offline;

use Payum\Action\CaptureDetailsAggregatedModelAction;
use Payum\Action\StatusDetailsAggregatedModelAction;
use Payum\Action\SyncDetailsAggregatedModelAction;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Action\StatusAction;
use Payum\Payment;

abstract class PaymentFactory
{
    /**
     * @return \Payum\Payment
     */
    public static function create()
    {
        $payment = new Payment;

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);

        $payment->addAction(new CaptureDetailsAggregatedModelAction);
        $payment->addAction(new SyncDetailsAggregatedModelAction);
        $payment->addAction(new StatusDetailsAggregatedModelAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}