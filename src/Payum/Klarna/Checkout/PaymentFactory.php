<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Payment;
use Payum\Core\PaymentFactoryInterface;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Action\StatusAction;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults(array(
            'merchantId' => '',
            'secret' => '',
            'contentType' => Constants::CONTENT_TYPE_V2_PLUS_JSON,
            'sandbox' => true,

        ));
        $options->validateNotEmpty(array('merchantId', 'secret'));

        $config = new Config();
        $config->merchantId = $options['merchantId'];
        $config->secret = $options['secret'];
        $config->contentType = $options['contentType'];
        $config->baseUri = $options['sandbox'] ?
            Constants::BASE_URI_SANDBOX :
            Constants::BASE_URI_LIVE
        ;

        $payment = new Payment;

        $payment->addApi($config);

        $payment->addAction(new AuthorizeAction('@PayumKlarnaCheckout/Action/capture.html.twig'));
        $payment->addAction(new NotifyAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new CreateOrderAction);
        $payment->addAction(new UpdateOrderAction);
        $payment->addAction(new RenderTemplateAction(TwigFactory::createGeneric(), '@PayumCore/layout.html.twig'));
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction());

        return $payment;
    }
}
