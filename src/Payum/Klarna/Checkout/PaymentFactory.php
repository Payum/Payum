<?php
namespace Payum\Klarna\Checkout;

use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Payment;
use Payum\Klarna\Checkout\Action\Api\CreateOrderAction;
use Payum\Klarna\Checkout\Action\Api\UpdateOrderAction;
use Payum\Klarna\Checkout\Action\CaptureAction;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param \Klarna_Checkout_ConnectorInterface $connector
     *
     * @return \Payum\Core\Payment
     */
    public static function create(\Klarna_Checkout_ConnectorInterface $connector, \Twig_Environment $twig)
    {
        $payment = new Payment;

        $payment->addApi($connector);

        $payment->addAction(new CaptureAction('@PayumKlarnaCheckout/Action/capture.html.twig'));
        $payment->addAction(new NotifyAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new CreateOrderAction);
        $payment->addAction(new UpdateOrderAction);
        $payment->addAction(new RenderTemplateAction($twig, '@PayumCore/layout.html.twig'));

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}
