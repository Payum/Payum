<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\AuthorizeNet\Aim\Action\FillOrderDetailsAction;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as BasePaymentFactory;

class PaymentFactory extends BasePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->validateNotEmpty(array('loginId', 'transactionKey'));

        $config->defaults(array(
            'sandbox' => true,

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
        ));

        $api = new AuthorizeNetAIM($config['loginId'], $config['transactionKey']);
        $api->setSandbox($config['sandbox']);
        $config['payum.api.default'] = $api;
    }
}