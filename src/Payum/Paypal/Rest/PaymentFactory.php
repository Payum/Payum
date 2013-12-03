<?php
namespace Payum\Paypal\Rest;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Action\StatusAction;
use PayPal\Rest\ApiContext;
use Payum\Paypal\Rest\Action\SyncAction;

abstract class PaymentFactory
{
    /**
     * @param ApiContext $api
     *
     * @return \Payum\Core\Payment
     */
    public static function create(ApiContext $api)
    {
        $payment = new Payment;

        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

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