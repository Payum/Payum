<?php
namespace Payum\Offline;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Action\StatusAction;
use Payum\Core\Payment;

abstract class PaymentFactory
{
    /**
     * @return \Payum\Core\Payment
     */
    public static function create()
    {
        $payment = new Payment;

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}