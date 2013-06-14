<?php
namespace Payum\Payex;

use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Action\Api\InitializeOrderAction;
use Payum\Payex\Action\CaptureAction;
use Payum\Payex\Action\StatusAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\CompleteOrderRequest;
use Payum\Payment;

abstract class PaymentFactory
{
    /**
     * @param Api\OrderApi $orderApi
     *
     * @return \Payum\Payment
     */
    public static function create(OrderApi $orderApi)
    {
        $payment = new Payment;

        $payment->addApi($orderApi);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new InitializeOrderAction);
        $payment->addAction(new CompleteOrderAction);
        
        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}