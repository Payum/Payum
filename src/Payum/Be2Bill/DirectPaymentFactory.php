<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\FillOrderDetailsAction;
use Payum\Be2Bill\Action\CaptureAction;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as BasePaymentFactory;

class DirectPaymentFactory extends BasePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->validateNotEmpty(array('identifier', 'password'));

        $config->defaults(array(
            'sandbox' => true,

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
        ));

        $config['payum.api.default'] = new Api(array(
            'identifier' => $config['identifier'],
            'password' => $config['password'],
            'sandbox' => $config['sandbox'],
        ));
    }
}