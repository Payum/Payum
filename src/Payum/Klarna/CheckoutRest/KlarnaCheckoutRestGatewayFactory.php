<?php
namespace Payum\Klarna\CheckoutRest;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Klarna\CheckoutRest\Action\Api\CreateOrderAction;
use Payum\Klarna\CheckoutRest\Action\Api\FetchOrderAction;
use Payum\Klarna\CheckoutRest\Action\Api\UpdateOrderAction;
use Payum\Klarna\CheckoutRest\Action\AuthorizeAction;
use Payum\Klarna\CheckoutRest\Action\AuthorizeRecurringAction;
use Payum\Klarna\CheckoutRest\Action\ConvertPaymentAction;
use Payum\Klarna\CheckoutRest\Action\NotifyAction;
use Payum\Klarna\CheckoutRest\Action\StatusAction;
use Payum\Klarna\CheckoutRest\Action\SyncAction;
use Payum\Klarna\Common\Config;
use Payum\Klarna\Common\Constants;

class KlarnaCheckoutRestGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (!class_exists('Klarna\Rest\Checkout\Order')) {
            throw new \LogicException('You must install "klarna/kco_rest" library.');
        }
        if (!class_exists('Payum\Klarna\Common\Action\Api\BaseApiAwareAction')) {
            throw new \LogicException('You must install "payum/klarna-common" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'klarna_checkout_rest',
            'payum.factory_title' => 'Klarna Checkout Rest',
            'payum.template.authorize' => '@PayumKlarnaCheckout/Action/capture.html.twig',
            'sandbox' => true,
        ));

        $config->defaults(array(
            'payum.action.authorize_recurring' => new AuthorizeRecurringAction(),

            // must be before authorize.
            'payum.action.authorize' => new AuthorizeAction($config['payum.template.authorize']),

            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_order' => new CreateOrderAction(),
            'payum.action.api.update_order' => new UpdateOrderAction(),
            'payum.action.api.fetch_order' => new FetchOrderAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'merchant_id' => '',
                'secret' => '',
                'terms_uri' => '',
                'checkout_uri' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('merchant_id', 'secret');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $klarnaConfig = new Config();
                $klarnaConfig->merchantId = $config['merchant_id'];
                $klarnaConfig->secret = $config['secret'];
                $klarnaConfig->termsUri = $config['terms'];
                $klarnaConfig->checkoutUri = $config['checkout'];
                $klarnaConfig->baseUri = $config['sandbox'] ?
                    Constants::BASE_URI_SANDBOX :
                    Constants::BASE_URI_LIVE
                ;

                return $klarnaConfig;
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumKlarnaCheckout' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
