<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;

class JsPaymentFactory extends CheckoutPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(array(
            'factory.name' => 'stripe_js',
            'factory.title' => 'Stripe.Js',

            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_js_token.html.twig',
        ));

        return parent::createConfig((array) $config);
    }
}
