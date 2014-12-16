<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;

class JsPaymentFactory extends CheckoutPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->defaults(array(
            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_js_token.html.twig'
        ));

        parent::build($payment, $config);
    }
}
