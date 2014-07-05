<?php
namespace Payum\Stripe;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentInterface;
use Payum\Stripe\Action\CaptureJsAction;
use Payum\Stripe\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param Keys $keys
     *
     * @return PaymentInterface
     */
    public static function createJs(Keys $keys, \Twig_Environment $twig)
    {
        $payment = new Payment;

        $payment->addApi($keys);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureJsAction('@PayumStripe/Action/capture_js.html.twig'));
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        $payment->addAction(new RenderTemplateAction($twig, '@PayumCore/layout.html.twig'));

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}