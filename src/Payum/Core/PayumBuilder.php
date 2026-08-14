<?php

namespace Payum\Core;

use Deprecated;
use DI\Container;
use DI\ContainerBuilder;
use DI\FactoryInterface;
use Exception;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Invoker\InvokerInterface;
use LogicException;
use Omnipay\Omnipay;
use Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Be2Bill\Be2BillOffsiteGatewayFactory;
use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Bridge\Twig\TwigRenderer;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\DI\CreatesGateway;
use Payum\Core\DI\FallbackContainer;
use Payum\Core\DI\ListableContainerInterface;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException as PayumLogicException;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway\DeclaresMiddleware;
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\Middleware\MiddlewareCollection;
use Payum\Core\Middleware\MiddlewareInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Payout;
use Payum\Core\Model\Token;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\FallbackRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Template\RendererInterface;
use Payum\Core\Template\TemplateRenderer;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;
use Payum\Offline\OfflineGatewayFactory;
use Payum\OmnipayBridge\OmnipayGatewayFactory;
use Payum\OmnipayV3Bridge\OmnipayGatewayFactory as OmnipayV3GatewayFactory;
use Payum\Payex\PayexGatewayFactory;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use Payum\Paypal\Masspay\Nvp\PaypalMasspayGatewayFactory;
use Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory;
use Payum\Paypal\ProHosted\Nvp\PaypalProHostedGatewayFactory;
use Payum\Paypal\Rest\PaypalRestGatewayFactory;
use Payum\Sofort\SofortGatewayFactory;
use Payum\Stripe\StripeCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use function array_merge;
use function array_replace;
use function DI\autowire;
use function in_array;
use function is_a;
use function is_dir;
use function is_file;
use function ltrim;
use function strtolower;
use function sys_get_temp_dir;
use function trigger_deprecation;

class PayumBuilder
{
    /**
     * @var HttpRequestVerifierInterface|callable|null
     */
    protected $httpRequestVerifier;

    /**
     * @var TokenFactoryInterface|callable|null
     */
    protected $tokenFactory;

    /**
     * @var GenericTokenFactoryInterface|callable|null
     */
    protected $genericTokenFactory;

    /**
     * @var string[]
     */
    protected array $genericTokenFactoryPaths = [];

    /**
     * @var ?StorageInterface<TokenInterface>
     */
    protected ?StorageInterface $tokenStorage = null;

    /**
     * @var GatewayFactoryInterface|callable|null
     */
    protected $coreGatewayFactory;

    /**
     * @var GatewayConfigInterface[]
     */
    protected array $coreGatewayFactoryConfig = [];

    /**
     * @var ?StorageInterface<GatewayConfigInterface>
     */
    protected ?StorageInterface $gatewayConfigStorage = null;

    /**
     * @var GatewayInterface[]|GatewayConfig[]
     */
    protected array $gateways = [];

    /**
     * @var array<string, mixed>
     */
    protected array $gatewayConfigs = [];

    /**
     * @var GatewayFactoryInterface[]|callable[]
     */
    protected array $gatewayFactories = [];

    /**
     * @var array<string, mixed>
     */
    protected array $gatewayFactoryConfigs = [];

    /**
     * @var StorageInterface<object>[]
     */
    protected array $storages = [];

    /**
     * @var ?RegistryInterface<object>
     */
    protected ?RegistryInterface $mainRegistry = null;

    protected MiddlewareCollection $middleware;

    protected ?ContainerInterface $globalContainer = null;

    /**
     * @var array<string, mixed>
     */
    protected array $globalDefinitions = [];

    /**
     * @var array<string, string>
     */
    protected array $templates = [];

    /**
     * @var array<string, RendererInterface>
     */
    protected array $renderers = [];

    protected string $layout = '@PayumCore/layout.html.twig';

    public function __construct()
    {
        $this->middleware = new MiddlewareCollection();
    }

    public function addDefaultStorages(): static
    {
        /** @var StorageInterface<TokenInterface> $tokenStorage */
        $tokenStorage = new FilesystemStorage(sys_get_temp_dir(), Token::class, 'hash');

        $this
            ->setTokenStorage($tokenStorage)

            ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
            ->addStorage(ArrayObject::class, new FilesystemStorage(sys_get_temp_dir(), ArrayObject::class))
            ->addStorage(Payout::class, new FilesystemStorage(sys_get_temp_dir(), Payout::class))
        ;

        return $this;
    }

    /**
     * @param class-string $modelClass
     * @param StorageInterface<object> $storage
     */
    public function addStorage(string $modelClass, StorageInterface $storage): static
    {
        $this->storages[$modelClass] = $storage;

        return $this;
    }

    /**
     * @deprecated addGateway is deprecated and will be removed in 2.0. Use registerGateway() instead.
     *
     * @param GatewayInterface|array<string, mixed> $gateway
     */
    #[Deprecated('addGateway is deprecated and will be removed in 2.0. Use registerGateway() instead.', '2.0.0')]
    public function addGateway(string $name, GatewayInterface | array $gateway): static
    {
        trigger_deprecation(
            'payum/core',
            '2.0.0',
            '%s is deprecated and will be removed in 2.0. Use %s instead.',
            __METHOD__,
            'registerGateway'
        );
        if ($gateway instanceof GatewayInterface) {
            $this->gateways[$name] = $gateway;
        } else {
            $currentConfig = $this->gatewayConfigs[$name] ?? [];
            $currentConfig = array_replace_recursive($currentConfig, $gateway);
            if (empty($currentConfig['factory'])) {
                throw new InvalidArgumentException('Gateway config must have factory set in it and it must not be empty.');
            }

            $this->gatewayConfigs[$name] = $currentConfig;
        }

        return $this;
    }

    public function addGatewayFactory(string $name, callable | GatewayFactoryInterface $gatewayFactory): static
    {
        $this->gatewayFactories[$name] = $gatewayFactory;

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function addGatewayFactoryConfig(string $name, array $config): static
    {
        $currentConfig = $this->gatewayFactoryConfigs[$name] ?? [];
        $this->gatewayFactoryConfigs[$name] = array_replace_recursive($currentConfig, $config);

        return $this;
    }

    public function setHttpRequestVerifier(HttpRequestVerifierInterface | callable | null $httpRequestVerifier = null): static
    {
        $this->httpRequestVerifier = $httpRequestVerifier;

        return $this;
    }

    public function setTokenFactory(callable | TokenFactoryInterface | null $tokenFactory = null): static
    {
        $this->tokenFactory = $tokenFactory;

        return $this;
    }

    public function setGenericTokenFactory(callable | GenericTokenFactoryInterface | null $tokenFactory = null): static
    {
        $this->genericTokenFactory = $tokenFactory;

        return $this;
    }

    /**
     * @param string[] $paths
     */
    public function setGenericTokenFactoryPaths(array $paths = []): static
    {
        $this->genericTokenFactoryPaths = $paths;

        return $this;
    }

    /**
     * @param ?StorageInterface<TokenInterface> $tokenStorage
     */
    public function setTokenStorage(?StorageInterface $tokenStorage = null): static
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    public function setCoreGatewayFactory(callable | GatewayFactoryInterface | null $coreGatewayFactory = null): static
    {
        $this->coreGatewayFactory = $coreGatewayFactory;

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     * @return $this
     */
    public function setCoreGatewayFactoryConfig(array $config = []): static
    {
        $this->coreGatewayFactoryConfig = $config;

        return $this;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function addCoreGatewayFactoryConfig(array $config): static
    {
        $currentConfig = $this->coreGatewayFactoryConfig ?: [];
        $this->coreGatewayFactoryConfig = array_replace_recursive($currentConfig, $config);

        return $this;
    }

    /**
     * @param StorageInterface<GatewayConfigInterface>|null $gatewayConfigStorage
     */
    public function setGatewayConfigStorage(?StorageInterface $gatewayConfigStorage = null): static
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;

        return $this;
    }

    /**
     * @param RegistryInterface<object>|null $mainRegistry
     */
    public function setMainRegistry(?RegistryInterface $mainRegistry = null): static
    {
        $this->mainRegistry = $mainRegistry;

        return $this;
    }

    /**
     * Add a global service definition that will be available to all gateways.
     * This allows sharing services (like HTTP clients, loggers) across all gateway instances.
     */
    /**
     * Registers middleware that wraps every command, on every gateway.
     *
     * This is where most middleware belongs: logging, locking and idempotency are not specific to any one
     * gateway. A package shipping middleware registers it here too, or contributes the container id and
     * lets the integration add it.
     *
     * @param class-string<MiddlewareInterface>|MiddlewareInterface $middleware a container id or an instance
     * @param int|null $priority higher runs further out. Defaults to what the middleware declares through
     *                           Payum\Core\Middleware\HasPriority, or 0
     */
    public function addMiddleware(string | MiddlewareInterface $middleware, ?int $priority = null): static
    {
        $this->middleware = $this->middleware->with($middleware, $priority);

        return $this;
    }

    public function addGlobalService(string $id, mixed $service): static
    {
        $this->globalDefinitions[$id] = $service;

        return $this;
    }

    /**
     * @param string $name a template key, or an engine-native name such as `@PayumAcme/checkout.html.twig`
     */
    public function setTemplate(string $name, string $file): static
    {
        $this->templates[$name] = $file;

        return $this;
    }

    /**
     * @param RendererInterface $renderer receives a resolved absolute file path, never a key
     */
    public function addRenderer(string $extension, RendererInterface $renderer): static
    {
        $this->renderers[ltrim($extension, '.')] = $renderer;

        return $this;
    }

    public function setLayout(string $layout): static
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Set a pre-built global container (for framework integration).
     * When set, this container will be used instead of building one from global definitions.
     */
    public function setGlobalContainer(ContainerInterface $container): static
    {
        $this->globalContainer = $container;

        return $this;
    }

    /**
     * @return Payum<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function getPayum(): Payum
    {
        $globalContainer = $this->buildGlobalContainer();

        $genericTokenFactory = $globalContainer->get(GenericTokenFactoryInterface::class);
        $httpRequestVerifier = $globalContainer->get(HttpRequestVerifierInterface::class);
        $tokenStorage = $this->tokenStorage ?? $globalContainer->get('payum.security.token_storage');

        /** @var CoreGatewayFactory $coreGatewayFactory */
        $coreGatewayFactory = $this->buildCoreGatewayFactory(array_replace_recursive([
            'payum.extension.token_factory' => new GenericTokenFactoryExtension($genericTokenFactory),
            'payum.security.token_storage' => $tokenStorage,
        ], $this->coreGatewayFactoryConfig));

        $gatewayFactories = array_replace(
            $this->buildGatewayFactories($coreGatewayFactory),
            $this->buildOmnipayGatewayFactories($coreGatewayFactory),
            $this->buildOmnipayV3GatewayFactories($coreGatewayFactory),
            $this->buildAddedGatewayFactories($coreGatewayFactory)
        );

        $gatewayFactories['core'] = $coreGatewayFactory;

        $gateways = [];

        foreach ($this->gateways as $name => $config) {
            if (! $config instanceof GatewayConfig) {
                continue;
            }

            $gatewayClass = $config->getGatewayClass();
            $gateway = new $gatewayClass();

            $gatewayDefinitions = $gateway instanceof ContainerConfiguration ? $gateway->configureContainer() : null;

            if (null !== $gatewayDefinitions && array_key_exists(RendererInterface::class, $gatewayDefinitions)) {
                throw new PayumLogicException(sprintf(
                    '%s declares %s. The renderer is registered by the application, not by a gateway: a gateway replacing it breaks every other gateway\'s templates. Use PayumBuilder::setTemplate() to override a template instead.',
                    $gatewayClass,
                    RendererInterface::class,
                ));
            }

            $containerBuilder = new ContainerBuilder();

            // Core defaults first, then the services shared by every gateway, then what the gateway
            // declares for itself. Last wins.
            $containerBuilder->addDefinitions($coreGatewayFactory->configureContainer());
            $containerBuilder->addDefinitions($this->buildSharedDefinitions($globalContainer));

            if (null !== $gatewayDefinitions) {
                $containerBuilder->addDefinitions($gatewayDefinitions);
            }

            $handlerDefinitions = array_map(autowire(...), HandlerMap::fromHandlers($gateway->handlers())->bindings());

            // Core defaults first, then what the application registered, then the gateway's own, so a
            // gateway's middleware runs innermost on equal priority.
            $middleware = CoreGatewayFactory::defaultMiddleware()->merge($this->middleware);

            if ($gateway instanceof DeclaresMiddleware) {
                foreach ($gateway->middleware() as $gatewayMiddleware) {
                    $middleware = $middleware->with($gatewayMiddleware);
                }
            }

            $containerBuilder->addDefinitions($handlerDefinitions);
            $containerBuilder->addDefinitions([
                MiddlewareCollection::class => $middleware,
            ]);
            $containerBuilder->addDefinitions([
                $config::class => $config,
                GatewayConfig::class => $config,
                $gatewayClass => $gateway,
                PaymentGateway::class => $gateway,
            ]);

            $gateways[$name] = $coreGatewayFactory->createGateway(
                new FallbackContainer($containerBuilder->build(), $globalContainer)
            );

            unset($this->gateways[$name]);
        }

        // Whatever is left in $this->gateways is a legacy instance rather than a config -- the loop above
        // unset each config as it built it. Merged unconditionally: these have to reach the registry even
        // when no gateway was registered by config.
        $gateways = array_merge($this->gateways, $gateways);

        if ($this->gatewayConfigs) {
            $factoryRegistry = $this->buildRegistry([], $this->storages, $gatewayFactories);
            foreach ($this->gatewayConfigs as $name => $gatewayConfig) {
                $gatewayFactory = $factoryRegistry->getGatewayFactory($gatewayConfig['factory']);
                unset($gatewayConfig['factory']);

                if ($gatewayFactory instanceof ContainerConfiguration) {
                    $containerBuilder = new ContainerBuilder();

                    // The gateway factory defaults come first, ...
                    $containerBuilder->addDefinitions($coreGatewayFactory->configureContainer());
                    $containerBuilder->addDefinitions($gatewayFactory->configureContainer());

                    // ... then the services shared by every gateway, so that they win over the factory
                    // defaults, ...
                    $containerBuilder->addDefinitions($this->buildSharedDefinitions($globalContainer));

                    // ... and finally the gateway config, which overrides both.
                    $containerBuilder->addDefinitions($gatewayConfig);

                    // Anything the gateway container does not know about is looked up globally, so that
                    // services of a container which cannot list its entries stay reachable too.
                    $container = new FallbackContainer($containerBuilder->build(), $globalContainer);

                    // Assembly is uniform unless a factory says otherwise, which is why CreatesGateway is
                    // separate from ContainerConfiguration: a gateway declares services without having to
                    // know how a Gateway is built.
                    $gateways[$name] = $gatewayFactory instanceof CreatesGateway
                        ? $gatewayFactory->createGateway($container)
                        : $coreGatewayFactory->createGateway($container);
                } else {
                    trigger_deprecation(
                        'payum/core',
                        '2.0.0',
                        'Not implementing %s for gateway factory %s is deprecated.',
                        ContainerConfiguration::class,
                        $gatewayFactory::class,
                    );

                    $gateways[$name] = $gatewayFactory->create($gatewayConfig);
                }
            }

        }

        // Every assembled gateway learns the name it is registered under, so anything reporting a
        // problem can say which gateway it happened on when the gateway is picked at runtime.
        foreach ($gateways as $gatewayName => $gateway) {
            if ($gateway instanceof Gateway) {
                $gateway->setName($gatewayName);
            }
        }

        $registry = $this->buildRegistry($gateways, $this->storages, $gatewayFactories);

        return new Payum($registry, $httpRequestVerifier, $genericTokenFactory, $tokenStorage);
    }

    /**
     * Registers a gateway from its typed config.
     *
     * The config names its descriptor, and the descriptor names everything else -- handlers, metadata,
     * the services it needs. Nothing about the gateway is spelled out here, which is the point: adding a
     * capability is adding a handler class, not editing the application's wiring.
     *
     * Container assembly and the command => handler map are built later, in getPayum().
     */
    public function registerGateway(string $name, GatewayConfig $config): self
    {
        // That this is a gateway is guaranteed by getGatewayClass()'s return type; re-checking would be
        // dead code. Instantiated bare, which every gateway must support -- see the note on
        // Payum\Core\Gateway\GatewayInterface about why metadata has to be readable without credentials.
        $gatewayClass = $config->getGatewayClass();
        $gateway = new $gatewayClass();

        // This, on the other hand, is the one mistake the type system cannot see -- registering, say, a
        // Paypal config against the Stripe gateway. Both sides only promise "a GatewayConfig".
        if (! is_a($config, $gateway->configClass())) {
            throw new LogicException(sprintf(
                '%s is configured by %s, but %s was given.',
                $gatewayClass,
                $gateway->configClass(),
                $config::class,
            ));
        }

        $this->gateways[$name] = $config;

        return $this;
    }

    /**
     * Build the global container with services shared across all gateways.
     * This includes HTTP clients, token storage, token factories, and storage extensions.
     *
     * A container set with setGlobalContainer() is put in front of it, so that an application only has to
     * declare the services it actually wants to provide itself and keeps Payum's defaults for the rest.
     *
     * @throws Exception
     */
    protected function buildGlobalContainer(): ContainerInterface
    {
        $tokenStorage = $this->resolveTokenStorage();

        /** @var StorageRegistryInterface<StorageInterface<TokenInterface>> $storageRegistry */
        $storageRegistry = $this->buildRegistry([], $this->storages);

        $paths = array_replace([
            'capture' => 'capture.php',
            'notify' => 'notify.php',
            'authorize' => 'authorize.php',
            'refund' => 'refund.php',
            'payout' => 'payout.php',
            'done' => 'done.php',
        ], $this->genericTokenFactoryPaths);

        $presetContainer = $this->globalContainer;

        [$templates, $namespaces] = $this->composeTemplates();

        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            'payum.security.token_storage' => $tokenStorage,

            // Payum's own token factory, unless the application brought its own. Resolving it here rather
            // than only in the fallback keeps the generic factory below built on top of the right one.
            TokenFactoryInterface::class => function () use ($presetContainer, $tokenStorage, $storageRegistry) {
                if ($presetContainer?->has(TokenFactoryInterface::class)) {
                    return $presetContainer->get(TokenFactoryInterface::class);
                }

                return $this->buildTokenFactory($tokenStorage, $storageRegistry);
            },
            GenericTokenFactoryInterface::class => fn (ContainerInterface $c): GenericTokenFactoryInterface => $this->buildGenericTokenFactory($c->get(TokenFactoryInterface::class), $paths),
            HttpRequestVerifierInterface::class => fn (): HttpRequestVerifierInterface => $this->buildHttpRequestVerifier($tokenStorage),

            // Storages only. A command carrying just a token needs this to load the payment its token
            // points at, and building it without gateways keeps it clear of the registry below.
            StorageRegistryInterface::class => $storageRegistry,
            ClientInterface::class => Psr18ClientDiscovery::find(...),
            StreamFactoryInterface::class => Psr17FactoryDiscovery::findStreamFactory(...),
            RequestFactoryInterface::class => Psr17FactoryDiscovery::findRequestFactory(...),

            RendererInterface::class => fn (): RendererInterface => new TemplateRenderer($templates, $this->composeRenderers($namespaces)),
        ]);

        foreach ($this->storages as $modelClass => $storage) {
            $builder->addDefinitions([
                $this->getStorageExtensionId($modelClass) => new StorageExtension($storage),
            ]);
        }

        $builder->addDefinitions($this->globalDefinitions);

        $container = $builder->build();

        if ($this->globalContainer) {
            $container = new FallbackContainer($this->globalContainer, $container);
        }

        return $container;
    }

    /**
     * The token storage in effect: the one set on the builder, else the one the application's container
     * provides, else Payum's default storages.
     *
     * @return StorageInterface<TokenInterface>
     */
    protected function resolveTokenStorage(): StorageInterface
    {
        if (! $this->tokenStorage && $this->globalContainer?->has('payum.security.token_storage')) {
            /** @var StorageInterface<TokenInterface> $tokenStorage */
            $tokenStorage = $this->globalContainer->get('payum.security.token_storage');

            return $tokenStorage;
        }

        if (! $this->tokenStorage) {
            $this->addDefaultStorages();
        }

        return $this->tokenStorage;
    }

    /**
     * Definitions delegating to the global container, so that every gateway resolves the very same
     * instance of a shared service.
     *
     * @return array<string, callable>
     */
    protected function buildSharedDefinitions(ContainerInterface $globalContainer): array
    {
        $ids = [
            'payum.security.token_storage',
            HttpRequestVerifierInterface::class,
            GenericTokenFactoryInterface::class,
            TokenFactoryInterface::class,
            StorageRegistryInterface::class,
            ClientInterface::class,
            StreamFactoryInterface::class,
            RequestFactoryInterface::class,
        ];

        foreach (array_keys($this->storages) as $modelClass) {
            $ids[] = $this->getStorageExtensionId($modelClass);
        }

        // Everything registered with addGlobalService() is shared as well.
        $ids = array_merge($ids, array_keys($this->globalDefinitions));

        // Plus the rest of what the global container is able to report.
        $ids = array_merge($ids, $this->getKnownEntryNames($globalContainer));

        $definitions = [];
        foreach (array_unique($ids) as $id) {
            $definitions[$id] = static fn () => $globalContainer->get($id);
        }

        return $definitions;
    }

    /**
     * The ids a container can report, if any. The container's own entries are left out, so that a gateway
     * keeps being injected with its own container rather than the global one.
     *
     * @return list<string>
     */
    protected function getKnownEntryNames(ContainerInterface $container): array
    {
        $names = [];

        if ($container instanceof ListableContainerInterface || $container instanceof Container) {
            $names = $container->getKnownEntryNames();
        }

        return array_values(array_diff($names, [
            ContainerInterface::class,
            Container::class,
            FactoryInterface::class,
            InvokerInterface::class,
        ]));
    }

    /**
     * @param class-string $modelClass
     */
    protected function getStorageExtensionId(string $modelClass): string
    {
        return 'payum.extension.storage_' . strtolower(str_replace('\\', '_', $modelClass));
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     * @param StorageRegistryInterface<StorageInterface<TokenInterface>> $storageRegistry
     */
    protected function buildTokenFactory(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry): TokenFactoryInterface
    {
        $tokenFactory = $this->tokenFactory;

        if (is_callable($tokenFactory)) {
            $tokenFactory = $tokenFactory($tokenStorage, $storageRegistry);

            if (! $tokenFactory instanceof TokenFactoryInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $tokenFactory ?: new TokenFactory($tokenStorage, $storageRegistry);
    }

    /**
     * @param string[] $paths
     */
    protected function buildGenericTokenFactory(TokenFactoryInterface $tokenFactory, array $paths): GenericTokenFactoryInterface
    {
        $genericTokenFactory = $this->genericTokenFactory;

        if (is_callable($genericTokenFactory)) {
            $genericTokenFactory = $genericTokenFactory($tokenFactory, $paths);

            if (! $genericTokenFactory instanceof GenericTokenFactoryInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $genericTokenFactory ?: new GenericTokenFactory($tokenFactory, $paths);
    }

    /**
     * @param array<string, GatewayInterface> $gateways
     * @param array<string, StorageInterface<object>> $storages
     * @return RegistryInterface<StorageRegistryInterface<StorageInterface<TokenInterface>>>
     */
    protected function buildRegistry(array $gateways = [], array $storages = [], array $gatewayFactories = []): RegistryInterface
    {
        $registry = new SimpleRegistry($gateways, $storages, $gatewayFactories);
        $registry->setAddStorageExtensions(false);

        if ($this->gatewayConfigStorage) {
            $dynamicRegistry = new DynamicRegistry($this->gatewayConfigStorage, $registry);
            $dynamicRegistry->setBackwardCompatibility(false);

            $registry = new FallbackRegistry($dynamicRegistry, $registry);
        }

        if ($this->mainRegistry) {
            $registry = new FallbackRegistry($this->mainRegistry, $registry);
        }

        /** @var RegistryInterface<StorageRegistryInterface<StorageInterface<TokenInterface>>> $registry */
        return $registry;
    }

    /**
     * @return GatewayFactoryInterface[]
     */
    protected function buildGatewayFactories(GatewayFactoryInterface $coreGatewayFactory): array
    {
        $map = [
            'paypal_express_checkout' => PaypalExpressCheckoutGatewayFactory::class,
            'paypal_pro_checkout' => PaypalProCheckoutGatewayFactory::class,
            'paypal_pro_hosted' => PaypalProHostedGatewayFactory::class,
            'paypal_masspay' => PaypalMasspayGatewayFactory::class,
            'paypal_rest' => PaypalRestGatewayFactory::class,
            'authorize_net_aim' => AuthorizeNetAimGatewayFactory::class,
            'be2bill_direct' => Be2BillDirectGatewayFactory::class,
            'be2bill_offsite' => Be2BillOffsiteGatewayFactory::class,
            'klarna_checkout' => KlarnaCheckoutGatewayFactory::class,
            'klarna_invoice' => KlarnaInvoiceGatewayFactory::class,
            'offline' => OfflineGatewayFactory::class,
            'payex' => PayexGatewayFactory::class,
            'stripe_checkout' => StripeCheckoutGatewayFactory::class,
            'stripe_js' => StripeJsGatewayFactory::class,
            'sofort' => SofortGatewayFactory::class,
        ];

        $gatewayFactories = [];

        foreach ($map as $name => $factoryClass) {
            if (class_exists($factoryClass)) {
                $gatewayFactories[$name] = new $factoryClass(
                    $this->gatewayFactoryConfigs[$name] ?? [],
                    $coreGatewayFactory
                );
            }
        }

        return $gatewayFactories;
    }

    /**
     * @return GatewayFactoryInterface[]
     */
    protected function buildAddedGatewayFactories(GatewayFactoryInterface $coreGatewayFactory): array
    {
        $gatewayFactories = [];
        foreach ($this->gatewayFactories as $name => $factory) {
            if (is_callable($factory)) {
                $config = $this->gatewayFactoryConfigs[$name] ?? [];

                $factory = $factory($config, $coreGatewayFactory);
            }

            $gatewayFactories[$name] = $factory;
        }

        return $gatewayFactories;
    }

    /**
     * @deprecated since 1.5 will be removed in 2.0
     *
     * @return array<string, object>
     */
    protected function buildOmnipayGatewayFactories(GatewayFactoryInterface $coreGatewayFactory): array
    {
        $gatewayFactories = [];
        if (! class_exists(Omnipay::class) || ! class_exists(OmnipayGatewayFactory::class)) {
            return $gatewayFactories;
        }

        $factory = Omnipay::getFactory();

        $gatewayFactories['omnipay'] = new OmnipayGatewayFactory('', $factory, [], $coreGatewayFactory);
        $gatewayFactories['omnipay_direct'] = new OmnipayGatewayFactory('', $factory, [], $coreGatewayFactory);
        $gatewayFactories['omnipay_offsite'] = new OmnipayGatewayFactory('', $factory, [], $coreGatewayFactory);

        if (method_exists($factory, 'getSupportedGateways')) {
            foreach ($factory->getSupportedGateways() as $type) {
                // omnipay throws exception on these gateways https://github.com/thephpleague/omnipay/issues/312
                // skip them for now
                if (in_array($type, ['Buckaroo', 'Alipay Bank', 'AliPay Dual Func', 'Alipay Express', 'Alipay Mobile Express', 'Alipay Secured', 'Alipay Wap Express', 'Cybersource', 'DataCash', 'Ecopayz', 'Neteller', 'Pacnet', 'PaymentSense', 'Realex Remote', 'SecPay (PayPoint.net)', 'Sisow', 'Skrill', 'YandexMoney', 'YandexMoneyIndividual'])) {
                    continue;
                }

                $gatewayFactories[strtolower('omnipay_' . $type)] = new OmnipayGatewayFactory($type, $factory, [], $coreGatewayFactory);
            }
        }

        return $gatewayFactories;
    }

    /**
     * @return GatewayFactoryInterface[]
     */
    protected function buildOmnipayV3GatewayFactories(GatewayFactoryInterface $coreGatewayFactory): array
    {
        $gatewayFactories = [];
        if (! class_exists(Omnipay::class) || ! class_exists(OmnipayV3GatewayFactory::class)) {
            return $gatewayFactories;
        }

        $factory = Omnipay::getFactory();

        $gatewayFactories['omnipay'] = new OmnipayV3GatewayFactory($factory, [], $coreGatewayFactory);

        return $gatewayFactories;
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    private function buildHttpRequestVerifier(StorageInterface $tokenStorage): HttpRequestVerifierInterface
    {
        $httpRequestVerifier = $this->httpRequestVerifier;

        if (is_callable($httpRequestVerifier)) {
            $httpRequestVerifier = $httpRequestVerifier($tokenStorage);

            if (! $httpRequestVerifier instanceof HttpRequestVerifierInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $httpRequestVerifier ?: new HttpRequestVerifier($tokenStorage);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function buildCoreGatewayFactory(array $config): GatewayFactoryInterface
    {
        $coreGatewayFactory = $this->coreGatewayFactory;

        $storages = $this->storages;
        foreach ($storages as $modelClass => $storage) {
            $extensionName = 'payum.extension.storage_' . strtolower(str_replace('\\', '_', $modelClass));

            $config[$extensionName] = new StorageExtension($storage);
        }

        if (is_callable($coreGatewayFactory)) {
            $coreGatewayFactory = $coreGatewayFactory($config);

            if (! $coreGatewayFactory instanceof GatewayFactoryInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $coreGatewayFactory ?: new CoreGatewayFactory($config);
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, list<string>>} keys => file, namespaces => directories
     */
    private function composeTemplates(): array
    {
        $templates = [];
        $namespaces = [];
        $declaredBy = [];

        foreach ($this->gateways as $config) {
            if (! $config instanceof GatewayConfig) {
                continue;
            }

            $gatewayClass = $config->getGatewayClass();
            $gateway = new $gatewayClass();

            if (! $gateway instanceof DeclaresTemplates) {
                continue;
            }

            foreach ($gateway->templates() as $name => $path) {
                if (is_dir($path)) {
                    $namespaces[$name][] = $path;

                    continue;
                }

                if (! is_file($path)) {
                    throw new PayumLogicException(sprintf(
                        '%s declares "%s" as %s, which is neither a file nor a directory.',
                        $gatewayClass,
                        $name,
                        $path,
                    ));
                }

                if (isset($declaredBy[$name]) && $declaredBy[$name] !== $gatewayClass) {
                    throw new PayumLogicException(sprintf(
                        'Template key "%s" is declared by both %s and %s.',
                        $name,
                        $declaredBy[$name],
                        $gatewayClass,
                    ));
                }

                $declaredBy[$name] = $gatewayClass;
                $templates[$name] = $path;
            }
        }

        return [array_replace($templates, $this->templates), $namespaces];
    }

    /**
     * @param array<string, list<string>> $namespaces
     *
     * @return array<string, RendererInterface>
     */
    private function composeRenderers(array $namespaces): array
    {
        $paths = array_replace([
            'PayumCore' => [__DIR__ . '/Resources/views'],
        ], $namespaces);

        return array_replace([
            'twig' => new TwigRenderer(new Environment(new ChainLoader()), $this->layout, $paths),
        ], $this->renderers);
    }
}
