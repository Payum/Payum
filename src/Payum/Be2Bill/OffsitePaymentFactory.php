<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;

class OffsitePaymentFactory extends DirectPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        parent::build($payment, $config);

        $config['payum.action.capture'] = new CaptureOffsiteAction;
    }
}