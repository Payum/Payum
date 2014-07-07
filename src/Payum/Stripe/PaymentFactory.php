<?php
namespace Payum\Stripe;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentInterface;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param Keys $keys
     *
     *
     * @return PaymentInterface
     */
    public static function createJs(
        Keys $keys,
        ActionInterface $renderTemplateAction = null,
        $layoutTemplate = null,
        $obtainTokenTemplate = null
    ) {
        $layoutTemplate = $layoutTemplate ?: '@PayumCore/layout.html.twig';
        $obtainTokenTemplate = $obtainTokenTemplate ?: '@PayumStripe/Action/obtain_js_token.html.twig';
        $renderTemplateAction = $renderTemplateAction ?: new RenderTemplateAction(TwigFactory::createGeneric(), $layoutTemplate);

        $payment = new Payment;

        $payment->addApi($keys);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction($renderTemplateAction);
        $payment->addAction(new ObtainTokenAction($obtainTokenTemplate));
        $payment->addAction(new CreateChargeAction);

        return $payment;
    }

    /**
     * @param Keys $keys
     *
     * @return PaymentInterface
     */
    public static function createCheckout(
        Keys $keys,
        ActionInterface $renderTemplateAction = null,
        $layoutTemplate = null,
        $obtainTokenTemplate = null
    ) {
        $layoutTemplate = $layoutTemplate ?: '@PayumCore/layout.html.twig';
        $obtainTokenTemplate = $obtainTokenTemplate ?: '@PayumStripe/Action/obtain_checkout_token.html.twig';
        $renderTemplateAction = $renderTemplateAction ?: new RenderTemplateAction(TwigFactory::createGeneric(), $layoutTemplate);

        $payment = new Payment;

        $payment->addApi($keys);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction($renderTemplateAction);
        $payment->addAction(new ObtainTokenAction($obtainTokenTemplate));
        $payment->addAction(new CreateChargeAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}