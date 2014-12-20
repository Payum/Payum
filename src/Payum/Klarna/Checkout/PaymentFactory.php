<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Action\Api\FetchOrderAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Action\StatusAction;
use Payum\Klarna\Checkout\Action\SyncAction;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);

        $config->defaults($this->corePaymentFactory->createConfig());

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

        if (false == $config['payum.api']) {
            $config['options.required'] = array('merchantId', 'secret');
            $config->defaults(array(
                'sandbox' => true,
            ));

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $klarnaConfig = new Config();
                $klarnaConfig->merchantId = $config['merchantId'];
                $klarnaConfig->secret = $config['secret'];
                $klarnaConfig->contentType = $config['contentType'];
                $klarnaConfig->baseUri = $config['sandbox'] ?
                    Constants::BASE_URI_SANDBOX :
                    Constants::BASE_URI_LIVE
                ;

                return $klarnaConfig;
            };
        }

        return (array) $config;
    }
}
