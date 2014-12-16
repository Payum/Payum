<?php
namespace Payum\Stripe;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\PaymentFactoryInterface;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Action\FillOrderDetailsAction;
use Payum\Stripe\Action\StatusAction;

class JsPaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults(array(
            'publishable_key' => '',
            'secret_key' => '',
        ));
        $options->validateNotEmpty(array('publishable_key', 'secret_key'));

        $payment = new Payment;

        $payment->addApi(new Keys($options['publishable_key'], $options['secret_key']));

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new RenderTemplateAction(TwigFactory::createGeneric(), '@PayumCore/layout.html.twig'));
        $payment->addAction(new ObtainTokenAction('@PayumStripe/Action/obtain_js_token.html.twig'));
        $payment->addAction(new CreateChargeAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }
}
