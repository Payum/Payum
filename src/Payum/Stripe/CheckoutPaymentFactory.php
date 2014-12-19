<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Action\FillOrderDetailsAction;
use Payum\Stripe\Action\StatusAction;

class CheckoutPaymentFactory extends CorePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->validateNotEmpty(array('publishable_key', 'secret_key'));

        $config->defaults(array(
            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_checkout_token.html.twig'
        ));

        $config->defaults(array(
            'payum.api' => new Keys($config['publishable_key'], $config['secret_key']),

            'payum.action.capture' => new CaptureAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.obtain_token' => new ObtainTokenAction($config['payum.template.obtain_token']),
            'payum.action.create_charge' => new CreateChargeAction(),
        ));
    }
}
