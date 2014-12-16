<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as BasePaymentFactory;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Action\StatusAction;
use Payum\Klarna\Checkout\Action\SyncAction;

class PaymentFactory extends BasePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->validateNotEmpty(array('merchantId', 'secret'));

        $config->defaults(array(
            'payum.template.authorize' => '@PayumKlarnaCheckout/Action/capture.html.twig',
            'contentType' => Constants::CONTENT_TYPE_V2_PLUS_JSON,
            'sandbox' => true,
        ));

        $config->defaults(array(
            'payum.action.authorize' => new AuthorizeAction($config['payum.template.authorize']),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.sync' => new SyncAction(),

            'payum.action.api.create_order' => new CreateOrderAction(),
            'payum.action.api.update_order' => new UpdateOrderAction(),
            'payum.action.api.fetch_order' => new FetchOrderAction(),
        ));


        $klarnaConfig = new Config();
        $klarnaConfig->merchantId = $config['merchantId'];
        $klarnaConfig->secret = $config['secret'];
        $klarnaConfig->contentType = $config['contentType'];
        $klarnaConfig->baseUri = $config['sandbox'] ?
            Constants::BASE_URI_SANDBOX :
            Constants::BASE_URI_LIVE
        ;
        $config->defaults(array('payum.api.default' => $klarnaConfig));
    }
}
