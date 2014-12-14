<?php
namespace Payum\Stripe;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentInterface;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Action\FillOrderDetailsAction;
use Payum\Stripe\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param Keys $keys
     * @param ActionInterface $renderTemplateAction
     * @param string $layoutTemplate
     * @param string $obtainTokenTemplate
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
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction($renderTemplateAction);
        $payment->addAction(new ObtainTokenAction($obtainTokenTemplate));
        $payment->addAction(new CreateChargeAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     * @param Keys $keys
     * @param ActionInterface $renderTemplateAction
     * @param string $layoutTemplate
     * @param string $obtainTokenTemplate
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
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction($renderTemplateAction);
        $payment->addAction(new ObtainTokenAction($obtainTokenTemplate));
        $payment->addAction(new CreateChargeAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
