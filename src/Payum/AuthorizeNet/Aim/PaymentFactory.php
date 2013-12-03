<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
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