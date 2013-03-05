<?php
namespace Payum\Bridge\Omnipay;

use Omnipay\Common\GatewayInterface;

use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Bridge\Omnipay\Action\CaptureAction;
use Payum\Bridge\Omnipay\Action\StatusAction;
use Payum\Payment;

abstract class PaymentFactory
{
    /**
     * @param \Omnipay\Common\GatewayInterface $gateway
     *
     * @return Payment
     */
    public static function create(GatewayInterface $gateway)
    {
        $payment = new Payment;
        
        $payment->addApi($gateway);
        
        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}