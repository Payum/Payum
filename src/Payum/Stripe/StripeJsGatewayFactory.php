<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;

class StripeJsGatewayFactory extends StripeCheckoutGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'stripe_js',
            'payum.factory_title' => 'Stripe.Js',

            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_js_token.html.twig',
        ));

        parent::populateConfig($config);
    }
}
