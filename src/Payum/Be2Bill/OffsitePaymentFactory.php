<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\FillOrderDetailsAction;
use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentFactoryInterface;

class OffsitePaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $payment = new Payment;

        $payment->addApi(new Api($options));

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureOffsiteAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }
}