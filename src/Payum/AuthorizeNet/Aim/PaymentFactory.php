<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\AuthorizeNet\Aim\Action\FillOrderDetailsAction;
use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Action\GenericOrderAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param AuthorizeNetAIM $api
     *
     * @return Payment
     */
    public static function create(AuthorizeNetAIM $api)
    {
        $payment = new Payment;

        $payment->addApi($api);
        
        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);
        $payment->addAction(new GenericOrderAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}