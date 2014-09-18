<?php
namespace Payum\Paypal\AdaptivePayments;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Paypal\AdaptivePayments\Action\Api\PayAction;
use Payum\Paypal\AdaptivePayments\Action\Api\PaymentDetailsAction;
use Payum\Paypal\AdaptivePayments\Action\CaptureAction;
use Payum\Paypal\AdaptivePayments\Action\StatusAction;
use Payum\Paypal\AdaptivePayments\Action\SyncAction;

abstract class PaymentFactory
{
    /**
     * @param Api $api
     *
     * @return \Payum\Core\Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment;

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new PayAction);
        $payment->addAction(new PaymentDetailsAction);
        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new SyncAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
