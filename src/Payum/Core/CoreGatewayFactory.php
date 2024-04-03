<?php

namespace Payum\Core;

use GuzzleHttp\Psr7\Request;
use Http\Adapter\Buzz\Client as HttpBuzzClient;
use Http\Adapter\Guzzle5\Client as HttpGuzzle5Client;
use Http\Adapter\Guzzle6\Client as HttpGuzzle6Client;
use Http\Adapter\Guzzle7\Client as HttpGuzzle7Client;
use Http\Client\Curl\Client as HttpCurlClient;
use Http\Client\Socket\Client as HttpSocketClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\MessageFactory\DiactorosMessageFactory;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use Http\Message\StreamFactory\GuzzleStreamFactory;
use LogicException;
use Nyholm\Psr7\Factory\HttplugFactory;
use Payum\Core\Action\AuthorizePaymentAction;
use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigUtil;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpClient\HttplugClient as SymfonyHttplugClient;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use function trigger_error;
use const E_USER_DEPRECATED;

class CoreGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [];

    /**
     * @param array<string, mixed> $defaultConfig
     */
    public function __construct(array $defaultConfig = [])
    {
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config = []): Gateway
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
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function createConfig(array $config = []): array
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);

        $config->defaults([
            'httplug.message_factory' => static function (ArrayObject $config): MessageFactory {
                @trigger_error('Using "httplug.message_factory" is deprecated, use "payum.http_message_factory" instead, which will return a PSR-17 RequestFactoryInterface since payum/core 2.0.0', E_USER_DEPRECATED);

                if (class_exists(MessageFactoryDiscovery::class)) {
                    return MessageFactoryDiscovery::find();
                }

                if (class_exists(Request::class)) {
                    return new GuzzleMessageFactory();
                }

                if (class_exists(\Laminas\Diactoros\Request::class)) {
                    return new DiactorosMessageFactory();
                }

                if (class_exists(\Nyholm\Psr7\Request::class)) {
                    return new HttplugFactory();
                }

                throw new LogicException('The httplug.message_factory could not be guessed. Install one of the following packages: php-http/guzzle7-adapter. You can also overwrite the config option with your implementation.');
            },
            'httplug.stream_factory' => static function (ArrayObject $config) {
                @trigger_error('Using "httplug.stream_factory" is deprecated, use "payum.http_stream_factory" instead which will return a PSR-17 StreamFactoryInterface since payum/core 2.0.0', E_USER_DEPRECATED);
                if (class_exists(StreamFactoryDiscovery::class)) {
                    return StreamFactoryDiscovery::find();
                }

                if (class_exists(Request::class)) {
                    return new GuzzleStreamFactory();
                }

                if (class_exists(\Nyholm\Psr7\Request::class)) {
                    return new HttplugFactory();
                }

                throw new LogicException('The httplug.stream_factory could not be guessed. Install one of the following packages: php-http/guzzle7-adapter. You can also overwrite the config option with your implementation.');
            },
            'httplug.client' => static function (ArrayObject $config) {
                @trigger_error('Using "httplug.client" is deprecated, use "payum.http_client" instead which will return a PSR-18 ClientInterface since payum/core 2.0.0', E_USER_DEPRECATED);

                if (class_exists(HttpClientDiscovery::class)) {
                    return HttpClientDiscovery::find();
                }

                if (class_exists(HttpGuzzle7Client::class)) {
                    return new HttpGuzzle7Client();
                }

                if (class_exists(HttpGuzzle6Client::class)) {
                    return new HttpGuzzle6Client();
                }

                if (class_exists(HttpGuzzle5Client::class)) {
                    return new HttpGuzzle5Client();
                }

                if (class_exists(SymfonyHttplugClient::class)) {
                    return new SymfonyHttplugClient();
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

                throw new LogicException('The httplug.client could not be guessed. Install one of the following packages: php-http/guzzle7-adapter, php-http/guzzle7-adapter. You can also overwrite the config option with your implementation.');
            },

            'payum.http_client' => static fn (ArrayObject $config): HttplugClient => new HttplugClient(Psr18ClientDiscovery::find()),
            'payum.http_stream_factory' => static fn (ArrayObject $config): StreamFactoryInterface => Psr17FactoryDiscovery::findStreamFactory(),
            'payum.http_message_factory' => static fn (ArrayObject $config): RequestFactoryInterface => Psr17FactoryDiscovery::findRequestFactory(),

            'payum.template.layout' => '@PayumCore/layout.html.twig',

            'twig.env' => fn () => new Environment(new ChainLoader()),
            'twig.register_paths' => function (ArrayObject $config) {
                $twig = $config['twig.env'];
                if (! $twig instanceof Environment) {
                    throw new LogicException(sprintf(
                        'The `twig.env config option must contains instance of Twig\Environment but got %s`',
                        get_debug_type($twig)
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
            'payum.action.render_template' => fn (ArrayObject $config) => new RenderTemplateAction($config['twig.env'], $config['payum.template.layout']),
            'payum.extension.endless_cycle_detector' => new EndlessCycleDetectorExtension(),
            'payum.action.get_currency' => fn (ArrayObject $config) => new GetCurrencyAction(),
            'payum.prepend_actions' => [],
            'payum.prepend_extensions' => [],
            'payum.prepend_apis' => [],
            'payum.default_options' => [],
            'payum.required_options' => [],

            'payum.api.http_client' => fn (ArrayObject $config) => $config['payum.http_client'],

            'payum.security.token_storage' => null,
        ]);

        if ($config['payum.security.token_storage']) {
            $config['payum.action.get_token'] = static fn (ArrayObject $config) => new GetTokenAction($config['payum.security.token_storage']);
        }

        $config['payum.paths'] = array_replace([
            'PayumCore' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []);

        return (array) $config;
    }

    protected function buildClosures(ArrayObject $config): void
    {
        // with higher priority
        foreach ([
            'payum.http_client',
            'payum.http_stream_factory',
            'payum.http_message_factory',

            'httplug.message_factory',
            'httplug.stream_factory',
            'httplug.client',

            'payum.paths',
            'twig.env',
            'twig.register_paths',
        ] as $name) {
            $value = $config[$name];
            if (is_callable($value)) {
                $config[$name] = $value($config);
            }
        }

        foreach ($config as $name => $value) {
            if (is_callable($value) && ! (is_string($value) && function_exists('\\' . $value))) {
                $config[$name] = $value($config);
            }
        }
    }

    protected function buildActions(Gateway $gateway, ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.action')) {
                $prepend = in_array($name, $config['payum.prepend_actions'], true);

                $gateway->addAction($value, $prepend);
            }
        }
    }

    protected function buildApis(Gateway $gateway, ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.api')) {
                @trigger_error('The payum.api.* config is deprecated and will be removed in 3.0. Use dependency-injection to inject the api into the action instead.', E_USER_DEPRECATED);
                $prepend = in_array($name, $config['payum.prepend_apis'], true);

                $gateway->addApi($value, $prepend);
            }
        }
    }

    protected function buildExtensions(Gateway $gateway, ArrayObject $config): void
    {
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.extension')) {
                $prepend = in_array($name, $config['payum.prepend_extensions'], true);

                $gateway->addExtension($value, $prepend);
            }
        }
    }
}
