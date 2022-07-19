<?php

namespace Payum\Core;

use LogicException;
use Omnipay\Omnipay;
use Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Be2Bill\Be2BillOffsiteGatewayFactory;
use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\Extension\StorageExtension;
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

class PayumBuilder
{
    /**
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
    protected $genericTokenFactoryPaths = [];

    /**
     * @var ?StorageInterface<TokenInterface>
     */
    protected $tokenStorage;

    /**
     * @var GatewayFactoryInterface|callable|null
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    protected $coreGatewayFactoryConfig = [];

    /**
     * @var StorageInterface<GatewayConfigInterface>
     */
    protected $gatewayConfigStorage;

    /**
     * @var GatewayInterface[]
     */
    protected $gateways = [];

    /**
     * @var array
     */
    protected $gatewayConfigs = [];

    /**
     * @var GatewayFactoryInterface[]|callable[]
     */
    protected $gatewayFactories = [];

    /**
     * @var array
     */
    protected $gatewayFactoryConfigs = [];

    /**
     * @var StorageInterface<object>[]
     */
    protected $storages = [];

    /**
     * @var RegistryInterface<object>
     */
    protected $mainRegistry;

    /**
     * @deprecated will be removed in 2.0
     *
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @return static
     */
    public function addDefaultStorages(): self
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
     *
     * @return static
     */
    public function addStorage(string $modelClass, StorageInterface $storage): self
    {
        // TODO add checks

        $this->storages[$modelClass] = $storage;

        return $this;
    }

    /**
     * @param GatewayInterface|array $gateway
     * @return static
     */
    public function addGateway(string $name, $gateway): self
    {
        // TODO add checks
        if ($gateway instanceof GatewayInterface) {
            $this->gateways[$name] = $gateway;
        } elseif (is_array($gateway)) {
            $currentConfig = $this->gatewayConfigs[$name] ?? [];
            $currentConfig = array_replace_recursive($currentConfig, $gateway);
            if (empty($currentConfig['factory'])) {
                throw new InvalidArgumentException('Gateway config must have factory set in it and it must not be empty.');
            }

            $this->gatewayConfigs[$name] = $currentConfig;
        } else {
            throw new LogicException('Gateway argument must be either instance of GatewayInterface or a config array');
        }

        return $this;
    }

    /**
     * @param GatewayFactoryInterface|callable $gatewayFactory
     * @return static
     */
    public function addGatewayFactory(string $name, $gatewayFactory): self
    {
        if (
            $gatewayFactory instanceof GatewayFactoryInterface ||
            is_callable($gatewayFactory)) {
            $this->gatewayFactories[$name] = $gatewayFactory;

            return $this;
        }

        throw new InvalidArgumentException('Invalid argument');
    }

    /**
     * @return static
     */
    public function addGatewayFactoryConfig(string $name, array $config): self
    {
        $currentConfig = $this->gatewayFactoryConfigs[$name] ?? [];
        $this->gatewayFactoryConfigs[$name] = array_replace_recursive($currentConfig, $config);

        return $this;
    }

    /**
     * @param HttpRequestVerifierInterface|callable|null $httpRequestVerifier
     *
     * @return static
     */
    public function setHttpRequestVerifier($httpRequestVerifier = null): self
    {
        if (
            null === $httpRequestVerifier ||
            $httpRequestVerifier instanceof HttpRequestVerifierInterface ||
            is_callable($httpRequestVerifier)) {
            $this->httpRequestVerifier = $httpRequestVerifier;

            return $this;
        }

        throw new InvalidArgumentException('Invalid argument');
    }

    /**
     * @param TokenFactoryInterface|callable|null $tokenFactory
     *
     * @return static
     */
    public function setTokenFactory($tokenFactory = null): self
    {
        if (
            null === $tokenFactory ||
            $tokenFactory instanceof TokenFactoryInterface ||
            is_callable($tokenFactory)) {
            $this->tokenFactory = $tokenFactory;

            return $this;
        }

        throw new InvalidArgumentException('Invalid argument');
    }

    /**
     * @param GenericTokenFactoryInterface|callable|null $tokenFactory
     *
     * @return static
     */
    public function setGenericTokenFactory($tokenFactory = null): self
    {
        if (
            null === $tokenFactory ||
            $tokenFactory instanceof GenericTokenFactoryInterface ||
            is_callable($tokenFactory)) {
            $this->genericTokenFactory = $tokenFactory;

            return $this;
        }

        throw new InvalidArgumentException('Invalid argument');
    }

    /**
     * @param \string[] $paths
     *
     * @return static
     */
    public function setGenericTokenFactoryPaths(array $paths = []): self
    {
        $this->genericTokenFactoryPaths = $paths;

        return $this;
    }

    /**
     * @param ?StorageInterface<TokenInterface> $tokenStorage
     *
     * @return static
     */
    public function setTokenStorage(StorageInterface $tokenStorage = null): self
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * @param GatewayFactoryInterface|callable|null $coreGatewayFactory
     *
     * @return static
     */
    public function setCoreGatewayFactory($coreGatewayFactory = null): self
    {
        if (
            null === $coreGatewayFactory ||
            $coreGatewayFactory instanceof GatewayFactoryInterface ||
            is_callable($coreGatewayFactory)) {
            $this->coreGatewayFactory = $coreGatewayFactory;

            return $this;
        }

        throw new InvalidArgumentException('Invalid argument');
    }

    /**
     * @param mixed[]|null $config
     *
     * @return static
     */
    public function setCoreGatewayFactoryConfig(array $config = null): self
    {
        $this->coreGatewayFactoryConfig = $config;

        return $this;
    }

    /**
     * @return static
     */
    public function addCoreGatewayFactoryConfig(array $config): self
    {
        $currentConfig = $this->coreGatewayFactoryConfig ?: [];
        $this->coreGatewayFactoryConfig = array_replace_recursive($currentConfig, $config);

        return $this;
    }

    /**
     * @param StorageInterface<GatewayConfigInterface>|null $gatewayConfigStorage
     *
     * @return static
     */
    public function setGatewayConfigStorage(StorageInterface $gatewayConfigStorage = null): self
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;

        return $this;
    }

    /**
     * @param RegistryInterface<object>|null $mainRegistry
     *
     * @return static
     */
    public function setMainRegistry(RegistryInterface $mainRegistry = null): self
    {
        $this->mainRegistry = $mainRegistry;

        return $this;
    }

    /**
     * @deprecated this method will be removed in 2.0 Use self::addCoreGatewayFactoryConfig to overwrite http client.
     *
     * @return static
     */
    public function setHttpClient(HttpClientInterface $httpClient = null): self
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return Payum<object>
     */
    public function getPayum(): Payum
    {
        if (! $this->tokenStorage) {
            throw new LogicException('Token storage must be configured.');
        }

        $storages = $this->storages;

        $tokenFactory = $this->buildTokenFactory($this->tokenStorage, $this->buildRegistry([], $storages));
        $genericTokenFactory = $this->buildGenericTokenFactory($tokenFactory, array_replace([
            'capture' => 'capture.php',
            'notify' => 'notify.php',
            'authorize' => 'authorize.php',
            'refund' => 'refund.php',
            'payout' => 'payout.php',
        ], $this->genericTokenFactoryPaths));

        $httpRequestVerifier = $this->buildHttpRequestVerifier($this->tokenStorage);

        $coreGatewayFactory = $this->buildCoreGatewayFactory(array_replace_recursive([
            'payum.extension.token_factory' => new GenericTokenFactoryExtension($genericTokenFactory),
            'payum.security.token_storage' => $this->tokenStorage,
        ], $this->coreGatewayFactoryConfig));

        $gatewayFactories = array_replace(
            $this->buildGatewayFactories($coreGatewayFactory),
            $this->buildOmnipayGatewayFactories($coreGatewayFactory),
            $this->buildOmnipayV3GatewayFactories($coreGatewayFactory),
            $this->buildAddedGatewayFactories($coreGatewayFactory)
        );

        $gatewayFactories['core'] = $coreGatewayFactory;

        $registry = $this->buildRegistry($this->gateways, $storages, $gatewayFactories);

        if ($this->gatewayConfigs) {
            $gateways = $this->gateways;
            foreach ($this->gatewayConfigs as $name => $gatewayConfig) {
                $gatewayFactory = $registry->getGatewayFactory($gatewayConfig['factory']);
                unset($gatewayConfig['factory']);

                $gateways[$name] = $gatewayFactory->create($gatewayConfig);
            }

            $registry = $this->buildRegistry($gateways, $storages, $gatewayFactories);
        }

        return new Payum($registry, $httpRequestVerifier, $genericTokenFactory, $this->tokenStorage);
    }

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     * @param StorageRegistryInterface<object> $storageRegistry
     */
    protected function buildTokenFactory(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry): TokenFactoryInterface
    {
        $tokenFactory = $this->tokenFactory;

        if (is_callable($tokenFactory)) {
            $tokenFactory = call_user_func($tokenFactory, $tokenStorage, $storageRegistry);

            if (false == $tokenFactory instanceof TokenFactoryInterface) {
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
            $genericTokenFactory = call_user_func($genericTokenFactory, $tokenFactory, $paths);

            if (false == $genericTokenFactory instanceof GenericTokenFactoryInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $genericTokenFactory ?: new GenericTokenFactory($tokenFactory, $paths);
    }

    /**
     * @param array<string, GatewayInterface> $gateways
     * @param array<string, StorageInterface<object>> $storages
     * @param array<string, GatewayFactoryInterface> $gatewayFactories
     * @return FallbackRegistry<object>
     */
    protected function buildRegistry(array $gateways = [], array $storages = [], array $gatewayFactories = []): FallbackRegistry
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

                $factory = call_user_func($factory, $config, $coreGatewayFactory);
            }

            $gatewayFactories[$name] = $factory;
        }

        return $gatewayFactories;
    }

    /**
     * @deprecated since 1.5 will be removed in 2.0
     *
     * @return GatewayFactoryInterface[]
     */
    protected function buildOmnipayGatewayFactories(GatewayFactoryInterface $coreGatewayFactory): array
    {
        $gatewayFactories = [];
        if (! class_exists(Omnipay::class) || class_exists(OmnipayGatewayFactory::class)) {
            return $gatewayFactories;
        }

        $factory = Omnipay::getFactory();

        $gatewayFactories['omnipay'] = new OmnipayGatewayFactory('', $factory, [], $coreGatewayFactory);
        $gatewayFactories['omnipay_direct'] = new OmnipayGatewayFactory('', $factory, [], $coreGatewayFactory);
        $gatewayFactories['omnipay_offsite'] = new OmnipayGatewayFactory('', $factory, [], $coreGatewayFactory);

        foreach ($factory->getSupportedGateways() as $type) {
            // omnipay throws exception on these gateways https://github.com/thephpleague/omnipay/issues/312
            // skip them for now
            if (in_array($type, ['Buckaroo', 'Alipay Bank', 'AliPay Dual Func', 'Alipay Express', 'Alipay Mobile Express', 'Alipay Secured', 'Alipay Wap Express', 'Cybersource', 'DataCash', 'Ecopayz', 'Neteller', 'Pacnet', 'PaymentSense', 'Realex Remote', 'SecPay (PayPoint.net)', 'Sisow', 'Skrill', 'YandexMoney', 'YandexMoneyIndividual'])) {
                continue;
            }

            $gatewayFactories[strtolower('omnipay_' . $type)] = new OmnipayGatewayFactory($type, $factory, [], $coreGatewayFactory);
        }

        return $gatewayFactories;
    }

    /**
     * @return GatewayFactoryInterface[]
     */
    protected function buildOmnipayV3GatewayFactories(GatewayFactoryInterface $coreGatewayFactory): array
    {
        $gatewayFactories = [];
        if (false == class_exists(Omnipay::class) || false == class_exists(OmnipayV3GatewayFactory::class)) {
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
            $httpRequestVerifier = call_user_func($httpRequestVerifier, $tokenStorage);

            if (false == $httpRequestVerifier instanceof HttpRequestVerifierInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $httpRequestVerifier ?: new HttpRequestVerifier($tokenStorage);
    }

    private function buildCoreGatewayFactory(array $config): GatewayFactoryInterface
    {
        $coreGatewayFactory = $this->coreGatewayFactory;

        $storages = $this->storages;
        foreach ($storages as $modelClass => $storage) {
            $extensionName = 'payum.extension.storage_' . strtolower(str_replace('\\', '_', $modelClass));

            $config[$extensionName] = new StorageExtension($storage);
        }

        if (is_callable($coreGatewayFactory)) {
            $coreGatewayFactory = call_user_func($coreGatewayFactory, $config);

            if (false == $coreGatewayFactory instanceof GatewayFactoryInterface) {
                throw new LogicException('Builder returned invalid instance');
            }
        }

        return $coreGatewayFactory ?: new CoreGatewayFactory($config);
    }
}
