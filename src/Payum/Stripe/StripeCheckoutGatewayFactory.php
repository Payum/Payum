<?php
namespace Payum\Stripe;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Action\Api\CreateCustomerAction;
use Payum\Stripe\Action\Api\CreatePlanAction;
use Payum\Stripe\Action\Api\CreateTokenAction;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Action\ConvertPaymentAction;
use Payum\Stripe\Action\GetCreditCardTokenAction;
use Payum\Stripe\Extension\CreateCustomerExtension;
use Payum\Stripe\Action\StatusAction;
use Stripe\Stripe;

class StripeCheckoutGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (false == class_exists(Stripe::class)) {
            throw new LogicException('You must install "stripe/stripe-php:~2.0|~3.0" library.');
        }

        $config->defaults([
            'payum.factory_name' => 'stripe_checkout',
            'payum.factory_title' => 'Stripe Checkout',

            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_checkout_token.html.twig',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.get_credit_card_token' => new GetCreditCardTokenAction(),
            'payum.action.obtain_token' => function (ArrayObject $config) {
                return new ObtainTokenAction($config['payum.template.obtain_token']);
            },
            'payum.action.create_charge' => new CreateChargeAction(),
            'payum.action.create_customer' => new CreateCustomerAction(),
            'payum.action.create_plan' => new CreatePlanAction(),
            'payum.action.create_token' => new CreateTokenAction(),

            'payum.extension.create_customer' => new CreateCustomerExtension(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'publishable_key' => '',
                'secret_key' => ''
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['publishable_key', 'secret_key'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Keys($config['publishable_key'], $config['secret_key']);
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumStripe' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
