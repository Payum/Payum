<?php
namespace Payum\Bridge\Omnipay;

use Omnipay\Common\GatewayInterface;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CapturePaymentInstructionAggregateAction;
use Payum\Action\StatusPaymentInstructionAggregateAction;
use Payum\Action\SyncPaymentInstructionAggregateAction;
use Payum\Bridge\Omnipay\Action\CaptureAction;
use Payum\Bridge\Omnipay\Action\StatusAction;

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
        $payment->addAction(new CapturePaymentInstructionAggregateAction);
        $payment->addAction(new SyncPaymentInstructionAggregateAction);
        $payment->addAction(new StatusPaymentInstructionAggregateAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}