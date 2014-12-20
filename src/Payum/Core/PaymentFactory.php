<?php
namespace Payum\Core;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Extension\EndlessCycleDetectorExtension;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $payment = new Payment();

        $this->buildActions($payment, $config);
        $this->buildApis($payment, $config);
        $this->buildExtensions($payment, $config);

        return $payment;
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(array(
            'payum.template.layout' => '@PayumCore/layout.html.twig',

            'buzz.client' => ClientFactory::createCurl(),
            'twig.env' => TwigFactory::createGeneric(),

            'payum.action.get_http_request' => new GetHttpRequestAction(),
            'payum.action.capture_order' => new CaptureOrderAction(),
            'payum.action.execute_same_request_with_model_details' => new ExecuteSameRequestWithModelDetailsAction(),
            'payum.action.render_template' => function (ArrayObject $config) {
                return new RenderTemplateAction($config['twig.env'], $config['payum.template.layout']);
            },
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
        ));

        return (array) $config;
    }

    /**
     * @param Payment     $payment
     * @param ArrayObject $config
     */
    protected function buildActions(Payment $payment, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.action')) {
                if (is_callable($value)) {
                    $payment->addAction(call_user_func_array($value, array($config)));
                } else {
                    $payment->addAction($value);
                }
            }
        }
    }

    /**
     * @param Payment     $payment
     * @param ArrayObject $config
     */
    protected function buildApis(Payment $payment, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.api')) {
                if (is_callable($value)) {
                    $payment->addApi(call_user_func_array($value, array($config)));
                } else {
                    $payment->addApi($value);
                }
            }
        }
    }

    /**
     * @param Payment     $payment
     * @param ArrayObject $config
     */
    protected function buildExtensions(Payment $payment, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.extension')) {
                if (is_callable($value)) {
                    $payment->addExtension(call_user_func_array($value, array($config)));
                } else {
                    $payment->addExtension($value);
                }
            }
        }
    }
}
