<?php
namespace Payum\OmnipayBridge;

use Omnipay\Common\GatewayInterface;

use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\StatusAction;
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