<?php
namespace Payum\Offline;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as BasePaymentFactory;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Action\FillOrderDetailsAction;
use Payum\Offline\Action\StatusAction;
use Payum\Core\Payment;

class PaymentFactory extends BasePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
        ));
    }
}