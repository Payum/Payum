<?php

namespace Payum\Core;

use DI\ContainerBuilder;
use Exception;
use GuzzleHttp\Psr7\Request;
use Http\Adapter\Buzz\Client as HttpBuzzClient;
use Http\Adapter\Guzzle5\Client as HttpGuzzle5Client;
use Http\Adapter\Guzzle6\Client as HttpGuzzle6Client;
use Http\Adapter\Guzzle7\Client as HttpGuzzle7Client;
use Http\Client\Curl\Client as HttpCurlClient;
use Http\Client\HttpClient;
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
use Payum\Core\Action\PrependActionInterface;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\Bridge\Twig\TwigUtil;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Extension\PrependExtensionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpClient\HttplugClient as SymfonyHttplugClient;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ChainLoader;
use function array_combine;
use function array_map;
use function array_merge;
use function class_exists;
use function DI\autowire;
use function DI\get;
use function in_array;
use function is_string;
use function sprintf;
use function trigger_error;
use const E_USER_DEPRECATED;

class CoreGatewayFactory implements GatewayFactoryInterface, ContainerConfiguration, GatewayFactoryConfigInterface
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
     *
     * @throws ContainerExceptionInterface | NotFoundExceptionInterface | Exception
     */
    public function create(array $config = []): Gateway
    {
        @trigger_error(sprintf('The %s is deprecated since 2.0. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class), E_USER_DEPRECATED);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($config);

        $container = $containerBuilder->build();

        $gateway = $this->createGateway($container);

        $entries = $container->getKnownEntryNames();

        $config = ArrayObject::ensureArrayObject(array_combine(
            $entries,
            array_map(static fn (string $name) => $container->get($name), $entries)
        ));

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
        @trigger_error(sprintf('The %s is deprecated since 2.0. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class), E_USER_DEPRECATED);

        return $this->configureContainer();
    }

    public function configureContainer(): array
    {
        return array_merge(
            $this->defaultConfig,
            [
                'httplug.message_factory' => static function (): MessageFactory {
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
                'httplug.stream_factory' => static function () {
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
                'httplug.client' => static function (ContainerInterface $config) {
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
                        return new HttpCurlClient($config->get('httplug.message_factory'), $config->get('httplug.stream_factory'));
                    }

                    if (class_exists(HttpBuzzClient::class)) {
                        return new HttpBuzzClient();
                    }

                    throw new LogicException('The httplug.client could not be guessed. Install one of the following packages: php-http/guzzle7-adapter, php-http/guzzle7-adapter. You can also overwrite the config option with your implementation.');
                },

                'payum.http_client' => static fn (): HttplugClient => new HttplugClient(Psr18ClientDiscovery::find()),
                'payum.http_stream_factory' => static fn (): StreamFactoryInterface => Psr17FactoryDiscovery::findStreamFactory(),
                'payum.http_message_factory' => static fn (): RequestFactoryInterface => Psr17FactoryDiscovery::findRequestFactory(),
                'payum.template.layout' => '@PayumCore/layout.html.twig',

                'twig.env' => static fn () => new Environment(new ChainLoader()),
                'payum.default_options' => [],
                'payum.required_options' => [],

                'payum.api.http_client' => get('payum.http_client'),

                'payum.security.token_storage' => null,

                'payum.paths' => [],

                ClientInterface::class => get('payum.http_client'),
                StreamFactoryInterface::class => get('payum.http_stream_factory'),
                RequestFactoryInterface::class => get('payum.http_message_factory'),
                ResponseFactoryInterface::class => get('payum.http_message_factory'),

                Environment::class => get('twig.env'),

                HttpClient::class => get('payum.http_client'),

                RenderTemplateAction::class => autowire()->constructor(layout: get('payum.template.layout')),
                GetTokenAction::class => autowire()->constructor(tokenStorage: get('payum.security.token_storage')),
            ]
        );
    }

    /**
     * @throws ContainerExceptionInterface | NotFoundExceptionInterface | LoaderError
     */
    public function createGateway(ContainerInterface $container): Gateway
    {
        TwigUtil::registerPaths(
            $container->get('twig.env'),
            array_merge(
                [
                    'PayumCore' => __DIR__ . '/Resources/views',
                ],
                (array) $container->get('payum.paths'),
            )
        );

        $gateway = new Gateway();

        foreach ($this->getActions() as $action) {
            if (is_string($action)) {
                $action = $container->get($action);
            }

            $gateway->addAction($action, $action instanceof PrependActionInterface);
        }

        foreach ($this->getExtensions() as $extension) {
            if (is_string($extension)) {
                $extension = $container->get($extension);
            }

            $gateway->addExtension($extension, $extension instanceof PrependExtensionInterface);
        }

        return $gateway;
    }

    public function getActions(): array
    {
        return [
            GetHttpRequestAction::class,
            CapturePaymentAction::class,
            AuthorizePaymentAction::class,
            PayoutPayoutAction::class,
            ExecuteSameRequestWithModelDetailsAction::class,
            RenderTemplateAction::class,
            GetCurrencyAction::class,
            GetTokenAction::class,
        ];
    }

    public function getExtensions(): array
    {
        return [
            EndlessCycleDetectorExtension::class,
        ];
    }

    /**
     * @deprecated since 2.0. Implement the ContainerConfiguration interface instead.
     */
    protected function buildClosures(ArrayObject $config): void
    {
        @trigger_error(sprintf('The %s is deprecated since 2.0. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class), E_USER_DEPRECATED);
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

    /**
     * @deprecated since 2.0. Implement the ContainerConfiguration interface instead.
     */
    protected function buildActions(Gateway $gateway, ArrayObject $config): void
    {
        @trigger_error(sprintf('The %s is deprecated since 2.0. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class), E_USER_DEPRECATED);
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.action')) {
                $prepend = in_array($name, $config['payum.prepend_actions'], true);

                $gateway->addAction($value, $prepend);
            }
        }
    }

    /**
     * @deprecated since 2.0. Implement the ContainerConfiguration interface instead.
     */
    protected function buildApis(Gateway $gateway, ArrayObject $config): void
    {
        @trigger_error(sprintf('The %s is deprecated since 2.0. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class), E_USER_DEPRECATED);
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.api')) {
                @trigger_error('The payum.api.* config is deprecated and will be removed in 3.0. Use dependency-injection to inject the api into the action instead.', E_USER_DEPRECATED);
                $prepend = in_array($name, $config['payum.prepend_apis'], true);

                $gateway->addApi($value, $prepend);
            }
        }
    }

    /**
     * @deprecated since 2.0. Implement the ContainerConfiguration interface instead.
     */
    protected function buildExtensions(Gateway $gateway, ArrayObject $config): void
    {
        @trigger_error(sprintf('The %s is deprecated since 2.0. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class), E_USER_DEPRECATED);
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.extension')) {
                $prepend = in_array($name, $config['payum.prepend_extensions'], true);

                $gateway->addExtension($value, $prepend);
            }
        }
    }
}
