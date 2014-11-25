<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Payment;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param Config $config
     * @param ActionInterface $renderTemplateAction
     * @param null $layoutTemplate
     * @param null $captureTemplate
     *
     * @return \Payum\Core\PaymentInterface
     */
    public static function create(
        Config $config,
        ActionInterface $renderTemplateAction = null,
        $layoutTemplate = null,
        $captureTemplate = null
    ) {
        $layoutTemplate = $layoutTemplate ?: '@PayumCore/layout.html.twig';
        $captureTemplate = $captureTemplate ?: '@PayumKlarnaCheckout/Action/capture.html.twig';
        $renderTemplateAction = $renderTemplateAction ?: new RenderTemplateAction(TwigFactory::createGeneric(), $layoutTemplate);
        $payment = new Payment;

        $payment->addApi($config);

        $payment->addAction(new AuthorizeAction($captureTemplate));
        $payment->addAction(new NotifyAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new CreateOrderAction);
        $payment->addAction(new UpdateOrderAction);
        $payment->addAction($renderTemplateAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction());

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
