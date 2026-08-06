<?php

namespace Payum\Core;

use Closure;
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
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionType;
use Symfony\Component\HttpClient\HttplugClient as SymfonyHttplugClient;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ChainLoader;
use function array_combine;
use function array_map;
use function array_merge;
use function class_exists;
use function func_get_args;
use function func_num_args;
use function in_array;
use function is_string;
use function str_starts_with;
use function trigger_deprecation;
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
        trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions($config);
        $containerBuilder->addDefinitions($this->configureContainer());
        $containerBuilder->addDefinitions([
            ArrayObject::class => static function (ContainerInterface $container) {
                trigger_deprecation('payum/core', '2.0.0', 'The %s service is deprecated and will be removed in 3.0. Use the %s service instead.', ArrayObject::class, ContainerInterface::class);
                return new class($container) extends ArrayObject {
                    private ContainerInterface $container;

                    public function __construct(ContainerInterface $container)
                    {
                        parent::__construct();

                        $this->container = $container;
                    }

                    public function offsetGet($key): mixed
                    {
                        return $this->container->get($key);
                    }

                    public function offsetExists($key): bool
                    {
                        return $this->container->has($key);
                    }
                };
            },
        ]);

        $container = $containerBuilder->build();

        $entries = $container->getKnownEntryNames();

        $configArray = ArrayObject::ensureArrayObject($config);
        $configArray->defaults(array_combine(
            $entries,
            array_map(static fn (string $name) => static fn () => $container->get($name), $entries)
        ));

        $gateway = new Gateway();

        $this->buildClosures($configArray);
        $this->buildActions($gateway, $configArray);
        $this->buildApis($gateway, $configArray);
        $this->buildExtensions($gateway, $configArray);

        return $this->createGateway($container, $gateway);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function createConfig(array $config = []): array
    {
        trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class);

        $containerConfig = $this->configureContainer();

        $result = array_merge($containerConfig, $config);

        if (isset($config['payum.paths'])) {
            $result['payum.paths'] = array_merge(
                $containerConfig['payum.paths'] ?? [],
                $config['payum.paths']
            );
        }

        return $result;
    }

    public function configureContainer(): array
    {
        return array_merge(
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

                // PSR-18/17 services - lazily discover if not provided
                ClientInterface::class => static fn (): ClientInterface => Psr18ClientDiscovery::find(),
                StreamFactoryInterface::class => static fn (): StreamFactoryInterface => Psr17FactoryDiscovery::findStreamFactory(),
                RequestFactoryInterface::class => static fn (): RequestFactoryInterface => Psr17FactoryDiscovery::findRequestFactory(),

                // Legacy Payum service names - delegate to PSR interfaces
                'payum.http_client' => static fn (ContainerInterface $c): HttplugClient => new HttplugClient($c->get(ClientInterface::class)),
                'payum.http_stream_factory' => static fn (ContainerInterface $c): StreamFactoryInterface => $c->get(StreamFactoryInterface::class),
                'payum.http_message_factory' => static fn (ContainerInterface $c): RequestFactoryInterface => $c->get(RequestFactoryInterface::class),
                'payum.template.layout' => '@PayumCore/layout.html.twig',

                'twig.env' => static fn () => new Environment(new ChainLoader()),
                'payum.default_options' => [],
                'payum.required_options' => [],

                // Backwards compatibility arrays for deprecated build* methods
                'payum.prepend_actions' => [],
                'payum.prepend_apis' => [],
                'payum.prepend_extensions' => [],

                'payum.api.http_client' => static fn (ContainerInterface $c) => $c->get('payum.http_client'),

                // Token storage - should be provided externally
                // 'payum.security.token_storage' => null,

                'payum.paths' => [
                    'PayumCore' => __DIR__ . '/Resources/views',
                ],

                // Additional aliases
                ResponseFactoryInterface::class => static fn (ContainerInterface $c): RequestFactoryInterface => $c->get(RequestFactoryInterface::class),
                Environment::class => static fn (ContainerInterface $c) => $c->get('twig.env'),
                HttpClient::class => static fn (ContainerInterface $c) => $c->get('payum.http_client'),

                // BC: Deprecated action entries (used by deprecated buildActions method)
                'payum.action.get_http_request' => static fn () => new GetHttpRequestAction(),
                'payum.action.capture_payment' => static fn () => new CapturePaymentAction(),
                'payum.action.authorize_payment' => static fn () => new AuthorizePaymentAction(),
                'payum.action.payout_payout' => static fn () => new PayoutPayoutAction(),
                'payum.action.execute_same_request_with_model_details' => static fn () => new ExecuteSameRequestWithModelDetailsAction(),
                'payum.action.get_currency' => static fn () => new GetCurrencyAction(),
                'payum.action.render_template' => static fn (ContainerInterface $c) => new RenderTemplateAction($c->get('twig.env'), $c->get('payum.template.layout')),
                'payum.action.get_token' => static fn (ContainerInterface $c) => $c->has('payum.security.token_storage')
                    ? new GetTokenAction($c->get('payum.security.token_storage'))
                    : null,

                // BC: Deprecated extension entries (used by deprecated buildExtensions method)
                'payum.extension.endless_cycle_detector' => static fn () => new EndlessCycleDetectorExtension(),

                // New DI approach: Action classes for createGateway()
                RenderTemplateAction::class => static fn (ContainerInterface $c) => new RenderTemplateAction($c->get('twig.env'), $c->get('payum.template.layout')),

                // GetTokenAction is conditionally added in createGateway() if token storage is available
                GetTokenAction::class => static fn (ContainerInterface $c) => $c->has('payum.security.token_storage')
                    ? new GetTokenAction($c->get('payum.security.token_storage'))
                    : throw new LogicException('Token storage must be configured to use GetTokenAction'),
            ],
            $this->defaultConfig,
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

        if (2 === func_num_args() && func_get_args()[1] instanceof Gateway) {
            $gateway = func_get_args()[1];
        } else {
            $gateway = new Gateway();
        }

        foreach ($this->getActions() as $action) {
            // Skip GetTokenAction if token storage is not configured
            if (GetTokenAction::class === $action && ! $container->has('payum.security.token_storage')) {
                continue;
            }

            $action = $container->get($action);

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
        trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class);

        // Helper to check if closure expects ArrayObject
        $canInvokeWithArrayObject = static function ($value): bool {
            if (! $value instanceof Closure) {
                return false;
            }

            $reflection = new ReflectionFunction($value);
            if (0 === $reflection->getNumberOfParameters()) {
                return true;
            }

            $params = $reflection->getParameters();
            $firstParam = $params[0];
            return ! $firstParam->getType() instanceof ReflectionType || ($firstParam->getType() instanceof ReflectionNamedType && ArrayObject::class === $firstParam->getType()->getName());
        };

        /** @var ContainerInterface $container */
        $container = $config[ContainerInterface::class]();

        foreach ($config as $name => $value) {
            if (GetTokenAction::class === $name && ! $container->has('payum.security.token_storage')) {
                continue;
            }

            if (is_callable($value) && ! (is_string($value) && function_exists('\\' . $value))) {
                if ($canInvokeWithArrayObject($value)) {
                    $config[$name] = $value($config);
                } else {
                    $config[$name] = $container->get($name);
                }
            }
        }
    }

    /**
     * @deprecated since 2.0. Implement the ContainerConfiguration interface instead.
     */
    protected function buildActions(Gateway $gateway, ArrayObject $config): void
    {
        trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class);
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.action') && null !== $value) {
                $prepend = in_array($name, $config['payum.prepend_actions'], true) || $value instanceof PrependActionInterface;

                $gateway->addAction($value, $prepend);
            }
        }
    }

    /**
     * @deprecated since 2.0. Implement the ContainerConfiguration interface instead.
     */
    protected function buildApis(Gateway $gateway, ArrayObject $config): void
    {
        trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class);
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
        trigger_deprecation('payum/core', '2.0.0', 'The %s is deprecated. Implement the %s interface instead.', __METHOD__, ContainerConfiguration::class);
        foreach ($config as $name => $value) {
            if (str_starts_with($name, 'payum.extension')) {
                $prepend = in_array($name, $config['payum.prepend_extensions'], true) || $value instanceof PrependExtensionInterface;

                $gateway->addExtension($value, $prepend);
            }
        }
    }
}
