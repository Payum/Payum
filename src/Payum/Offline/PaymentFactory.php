<?php
namespace Payum\Offline;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentFactoryInterface;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Action\FillOrderDetailsAction;
use Payum\Offline\Action\StatusAction;
use Payum\Core\Payment;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $payment = new Payment;

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }
}