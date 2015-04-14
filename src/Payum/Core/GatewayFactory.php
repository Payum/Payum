<?php
namespace Payum\Core;

use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigFactory;
use Payum\Core\Extension\EndlessCycleDetectorExtension;

class GatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var array
     */
    protected $defaultConfig;

    /**
     * @param array $defaultConfig
     */
    public function __construct(array $defaultConfig = array())
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->createConfig());

        $gateway = new Gateway();

        $this->buildActions($gateway, $config);
        $this->buildApis($gateway, $config);
        $this->buildExtensions($gateway, $config);

        return $gateway;
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults(array(
            'payum.template.layout' => '@PayumCore/layout.html.twig',

            'buzz.client' => ClientFactory::createCurl(),
            'twig.env' => TwigFactory::createGeneric(),

            'payum.action.get_http_request' => new GetHttpRequestAction(),
            'payum.action.capture_payment' => new CapturePaymentAction(),
            'payum.action.execute_same_request_with_model_details' => new ExecuteSameRequestWithModelDetailsAction(),
            'payum.action.render_template' => function (ArrayObject $config) {
                return new RenderTemplateAction($config['twig.env'], $config['payum.template.layout']);
            },
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
            'payum.action.get_currency' => function (ArrayObject $config) {
                return new GetCurrencyAction($config['payum.iso4217']);
            },
            'payum.prepend_actions' => array(),
            'payum.prepend_extensions' => array(),
            'payum.prepend_apis' => array(),
            'payum.default_options' => array(),
            'payum.required_options' => array(),
        ));

        return (array) $config;
    }

    /**
     * @param Gateway     $gateway
     * @param ArrayObject $config
     */
    protected function buildActions(Gateway $gateway, ArrayObject $config)
    {

        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.action')) {
                $prepend = in_array($name, $config['payum.prepend_actions']);

                if (is_callable($value)) {
                    $gateway->addAction(call_user_func_array($value, array($config)), $prepend);
                } else {
                    $gateway->addAction($value, $prepend);
                }
            }
        }
    }

    /**
     * @param Gateway     $gateway
     * @param ArrayObject $config
     */
    protected function buildApis(Gateway $gateway, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.api')) {
                $prepend = in_array($name, $config['payum.prepend_apis']);

                if (is_callable($value)) {
                    $gateway->addApi(call_user_func_array($value, array($config)), $prepend);
                } else {
                    $gateway->addApi($value, $prepend);
                }
            }
        }
    }

    /**
     * @param Gateway     $gateway
     * @param ArrayObject $config
     */
    protected function buildExtensions(Gateway $gateway, ArrayObject $config)
    {
        foreach ($config as $name => $value) {
            if (0 === strpos($name, 'payum.extension')) {
                $prepend = in_array($name, $config['payum.prepend_extensions']);

                if (is_callable($value)) {
                    $gateway->addExtension(call_user_func_array($value, array($config)), $prepend);
                } else {
                    $gateway->addExtension($value, $prepend);
                }
            }
        }
    }
}
