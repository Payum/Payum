<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Stripe\Action\Api\ObtainTokenForCreditCardAction;

class StripeDirectGatewayFactory extends StripeCheckoutGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'stripe_direct',
            'payum.factory_title' => 'Stripe Direct',

            'payum.action.obtain_token' => new ObtainTokenForCreditCardAction(),
        ]);

        parent::populateConfig($config);
    }
}
