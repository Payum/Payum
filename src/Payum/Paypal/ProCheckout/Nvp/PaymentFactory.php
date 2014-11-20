<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Action\NotifyOrderAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;

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

        $payment->addAction(new CaptureAction);
        $payment->addAction(new CaptureOrderAction);
        $payment->addAction(new NotifyOrderAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction(new GetHttpRequestAction);
       
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
