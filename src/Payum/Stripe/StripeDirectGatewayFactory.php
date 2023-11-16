<?php

namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Stripe\Action\Api\ObtainTokenForCreditCardAction;

class StripeDirectGatewayFactory extends StripeCheckoutGatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'stripe_direct',
            'payum.factory_title' => 'Stripe Direct',

            'payum.action.obtain_token' => new ObtainTokenForCreditCardAction(),
        ]);

        parent::populateConfig($config);
    }
}
