<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Action\AuthorizeRecurringAction;
use Payum\Klarna\Checkout\Action\ConvertPaymentAction;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Action\StatusAction;
use Payum\Klarna\Checkout\Action\SyncAction;

class KlarnaCheckoutGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (!class_exists('Klarna_Checkout_Order')) {
            throw new \LogicException('You must install "klarna/checkout" library.');
        }

        $config->defaults(array(
            'payum.factory_name' => 'klarna_checkout',
            'payum.factory_title' => 'Klarna Checkout',
            'payum.template.authorize' => '@PayumKlarnaCheckout/Action/capture.html.twig',
            'contentType' => Constants::CONTENT_TYPE_AGGREGATED_ORDER_V2,
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
                $klarnaConfig->contentType = $config['contentType'];
                $klarnaConfig->termsUri = $config['termsUri'] ?: $config['terms_uri'];
                $klarnaConfig->checkoutUri = $config['checkoutUri'] ?: $config['checkout_uri'];
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
