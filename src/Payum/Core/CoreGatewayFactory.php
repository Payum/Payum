<?php
namespace Payum\Core;

use Http\Adapter\Guzzle6\Client as HttpGuzzle6Client;
use Http\Adapter\Guzzle5\Client as HttpGuzzle5Client;
use Http\Adapter\Buzz\Client as HttpBuzzClient;
use Http\Client\Curl\Client as HttpCurlClient;
use Http\Client\Socket\Client as HttpSocketClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Message\MessageFactory\DiactorosMessageFactory;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\StreamFactory\DiactorosStreamFactory;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use Payum\Core\Action\AuthorizePaymentAction;
use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigUtil;
use Payum\Core\Extension\EndlessCycleDetectorExtension;

class CoreGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var array
     */
    protected $defaultConfig;

    /**
     * @param array $defaultConfig
     */
    public function __construct(array $defaultConfig = [])
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = [])
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->createConfig());

        $gateway = new Gateway();

        $this->buildClosures($config);

        $this->buildActions($gateway, $config);
        $this->buildApis($gateway, $config);
        $this->buildExtensions($gateway, $config);

        return $gateway;
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = [])
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);

        $config->defaults([
            'httplug.message_factory'=>function (ArrayObject $config) {
                if (class_exists(MessageFactoryDiscovery::class)) {
                    return MessageFactoryDiscovery::find();
                }

                if (class_exists(\GuzzleHttp\Psr7\Request::class)) {
                    return new GuzzleMessageFactory();
                }

                if (class_exists(\Zend\Diactoros\Request::class)) {
                    return new DiactorosMessageFactory();
                }

                throw new \LogicException('The httplug.message_factory could not be guessed. Install one of the following packages: php-http/guzzle6-adapter, zendframework/zend-diactoros. You can also overwrite the config option with your implementation.');
            },
            'httplug.stream_factory'=>function (ArrayObject $config) {
                if (class_exists(StreamFactoryDiscovery::class)) {
                    return StreamFactoryDiscovery::find();
                }

                if (class_exists(\GuzzleHttp\Psr7\Request::class)) {
                    return new GuzzleStreamFactory();
                }

                if (class_exists(\Zend\Diactoros\Request::class)) {
                    return new DiactorosStreamFactory();
                }

                throw new \LogicException('The httplug.stream_factory could not be guessed. Install one of the following packages: php-http/guzzle6-adapter, zendframework/zend-diactoros. You can also overwrite the config option with your implementation.');
            },
            'httplug.client'=>function (ArrayObject $config) {
                if (class_exists(HttpClientDiscovery::class)) {
                    return HttpClientDiscovery::find();
                }

                if (class_exists(HttpGuzzle6Client::class)) {
                    return new HttpGuzzle6Client();
                }

                if (class_exists(HttpGuzzle5Client::class)) {
                    return new HttpGuzzle5Client();
                }

                if (class_exists(HttpSocketClient::class)) {
                    return new HttpSocketClient();
                }

                if (class_exists(HttpCurlClient::class)) {
                    return new HttpCurlClient($config['httplug.message_factory'], $config['httplug.stream_factory']);
                }

                if (class_exists(HttpBuzzClient::class)) {
                    return new HttpBuzzClient();
                }

                throw new \LogicException('The httplug.client could not be guessed. Install one of the following packages: php-http/guzzle6-adapter. You can also overwrite the config option with your implementation.');
            },
            'payum.http_client'=>function (ArrayObject $config) {
                return new HttplugClient($config['httplug.client']);
            },
            'payum.template.layout' => '@PayumCore/layout.html.twig',

            'twig.env' => function () {
                return new \Twig_Environment(new \Twig_Loader_Chain());
            },
            'twig.register_paths' => function (ArrayObject $config) {
                $twig = $config['twig.env'];
                if (false == $twig instanceof \Twig_Environment) {
                    throw new \LogicException(sprintf(
                        'The `twig.env config option must contains instance of Twig_Environment but got %s`',
                        is_object($twig) ? get_class($twig) : gettype($twig)
                    ));
                }

                TwigUtil::registerPaths($twig, $config['payum.paths']);

                return null;
            },
            'payum.action.get_http_request' => new GetHttpRequestAction(),
            'payum.action.capture_payment' => new CapturePaymentAction(),
            'payum.action.authorize_payment' => new AuthorizePaymentAction(),
            'payum.action.payout_payout' => new PayoutPayoutAction(),
            'payum.action.execute_same_request_with_model_details' => new ExecuteSameRequestWithModelDetailsAction(),
            'payum.action.render_template' => function (ArrayObject $config) {
                return new RenderTemplateAction($config['twig.env'], $config['payum.template.layout']);
            },
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
            'payum.action.get_currency' => function (ArrayObject $config) {
                return new GetCurrencyAction($config['payum.iso4217']);
            },
            'payum.prepend_actions' => [],
            'payum.prepend_extensions' => [],
            'payum.prepend_apis' => [],
            'payum.default_options' => [],
            'payum.required_options' => [],

            'payum.api.http_client' => function (ArrayObject $config) {
                return $config['payum.http_client'];
            },

            'payum.security.token_storage' => null,
        ]);

        if ($config['payum.security.token_storage']) {
            $config['payum.action.get_token'] = function (ArrayObject $config) {
                return new GetTokenAction($config['payum.security.token_storage']);
            };
        }

        $config['payum.paths'] = array_replace([
            'PayumCore' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);

        return (array) $config;
    }

    /**
     * @param ArrayObject $config
     */
    protected function buildClosures(ArrayObject $config)
    {
        // with higher priority
        foreach (['httplug.message_factory', 'httplug.stream_factory', 'httplug.client', 'payum.http_client', 'payum.paths', 'twig.env', 'twig.register_paths'] as $name) {
            $value = $config[$name];
            if (is_callable($value)) {
                $config[$name] = call_user_func($value, $config);
            }
        }

        foreach ($config as $name => $value) {
            if (is_callable($value)) {
                $config[$name] = call_user_func($value, $config);
            }
        }
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

                $gateway->addAction($value, $prepend);
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

                $gateway->addApi($value, $prepend);
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

                $gateway->addExtension($value, $prepend);
            }
        }
    }
}
