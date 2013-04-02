<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CaptureDetailsAggregatedModelAction;
use Payum\Action\StatusDetailsAggregatedModelAction;
use Payum\Action\SyncDetailsAggregatedModelAction;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ProCheckout\Nvp\Api;

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
        
        $payment->addAction(new CaptureDetailsAggregatedModelAction);
        $payment->addAction(new SyncDetailsAggregatedModelAction);
        $payment->addAction(new StatusDetailsAggregatedModelAction);
       
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
