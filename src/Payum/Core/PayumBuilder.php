<?php
namespace Payum\Core;

use Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Be2Bill\Be2BillOffsiteGatewayFactory;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Model\ArrayObject;
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
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;
use Payum\Offline\OfflineGatewayFactory;
use Payum\OmnipayBridge\OmnipayGatewayFactory;
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
    protected $genericTokenFactoryPaths = array();

    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var GatewayFactoryInterface|callable|null
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    protected $coreGatewayFactoryConfig = array();

    /**
     * @var StorageInterface
     */
    protected $gatewayConfigStorage;

    /**
     * @var GatewayInterface[]
     */
    protected $gateways = array();

    /**
     * @var array
     */
    protected $gatewayConfigs = array();

    /**
     * @var GatewayFactoryInterface[]|callable[]
     */
    protected $gatewayFactories = array();

    /**
     * @var array
     */
    protected $gatewayFactoryConfigs = array();

    /**
     * @var StorageInterface[]
     */
    protected $storages = array();

    /**
     * @var RegistryInterface
     */
    protected $mainRegistry;

    /**
     * @deprecated will be removed in 2.0
     *
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $modelIdProperty;

    /**
     * @var string
     */
    protected $ConfigPath;

    /**
     * @return static
     */
    public function addConfigPath($config)
    {
        $this->ConfigPath = $config;

        return $this;
    }

    /**
     * @return static
     */
    public function addDefaultStorages()
    {
        $this
        ->setTokenStorage(new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Model\Token', 'hash'))
        ->addStorage('Payum\Core\Model\Payment', new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Model\Payment', 'number'))
        //->addStorage('Payum\Core\Bridge\Spl\ArrayObject', new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Bridge\Spl\ArrayObject'))
        ->addStorage('Payum\Core\Model\ArrayObject', new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Model\ArrayObject'))
        ;

        return $this;
    }

    /**
     * @param string           $modelClass
     * @param StorageInterface $storage
     *
     * @return static
     */
    public function addStorage($modelClass, StorageInterface $storage)
    {
        // TODO add checks

        $this->storages[$modelClass] = $storage;

        return $this;
    }

    /**
     * @param string           $name
     * @param GatewayInterface|array $gateway
     *
     * @return static
     */
    public function addGateway($name, $gateway)
    {
        // TODO add checks
        if ($gateway instanceof GatewayInterface) {
            $this->gateways[$name] = $gateway;
        } elseif (is_array($gateway)) {
            if (empty($gateway['factory'])) {
                throw new \InvalidArgumentException('Gateway config must have factory key and it must not be empty.');
            }

            $this->gatewayConfigs[$name] = $gateway;
        } else {
            throw new \LogicException('Gateway argument must be either instance of GatewayInterface or a config array');
        }

        return $this;
    }

    /**
     * @param string           $name
     * @param GatewayFactoryInterface $gatewayFactory
     *
     * @return static
     */
    public function addGatewayFactory($name, GatewayFactoryInterface $gatewayFactory)
    {
        // TODO add checks

        $this->gatewayFactories[$name] = $gatewayFactory;

        return $this;
    }

    /**
     * @param string $name
     * @param array  $config
     *
     * @return static
     */
    public function addGatewayFactoryConfig($name, array $config)
    {
        // TODO add checks

        $this->gatewayFactoryConfigs[$name] = $config;

        return $this;
    }

    /**
     * @param HttpRequestVerifierInterface|callable|null $httpRequestVerifier
     *
     * @return static
     */
    public function setHttpRequestVerifier($httpRequestVerifier = null)
    {
        if (
            null === $httpRequestVerifier ||
            $httpRequestVerifier instanceof HttpRequestVerifierInterface ||
            is_callable($httpRequestVerifier))
        {
            $this->httpRequestVerifier = $httpRequestVerifier;

            return $this;
        }

        throw new \InvalidArgumentException('Invalid argument');
    }

    /**
     * @param TokenFactoryInterface|callable|null $tokenFactory
     *
     * @return static
     */
    public function setTokenFactory($tokenFactory = null)
    {
        if (
            null === $tokenFactory ||
            $tokenFactory instanceof TokenFactoryInterface ||
            is_callable($tokenFactory))
        {
            $this->tokenFactory = $tokenFactory;

            return $this;
        }

        throw new \InvalidArgumentException('Invalid argument');
    }

    /**
     * @param GenericTokenFactoryInterface|callable|null $tokenFactory
     *
     * @return static
     */
    public function setGenericTokenFactory($tokenFactory = null)
    {
        if (
            null === $tokenFactory ||
            $tokenFactory instanceof GenericTokenFactoryInterface ||
            is_callable($tokenFactory))
        {
            $this->genericTokenFactory = $tokenFactory;

            return $this;
        }

        throw new \InvalidArgumentException('Invalid argument');
    }

    /**
     * @param \string[] $paths
     *
     * @return static
     */
    public function setGenericTokenFactoryPaths(array $paths = array())
    {
        $this->genericTokenFactoryPaths = $paths;

        return $this;
    }

    /**
     * @param StorageInterface $tokenStorage
     *
     * @return static
     */
    public function setTokenStorage(StorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * @param GatewayFactoryInterface|callable|null $coreGatewayFactory
     *
     * @return static
     */
    public function setCoreGatewayFactory($coreGatewayFactory = null)
    {
        if (
            null === $coreGatewayFactory ||
            $coreGatewayFactory instanceof GatewayFactoryInterface ||
            is_callable($coreGatewayFactory))
        {
            $this->coreGatewayFactory = $coreGatewayFactory;

            return $this;
        }

        throw new \InvalidArgumentException('Invalid argument');
    }

    /**
     * @param array $config
     *
     * @return static
     */
    public function setCoreGatewayFactoryConfig(array $config = null)
    {
        $this->coreGatewayFactoryConfig = $config;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return static
     */
    public function addCoreGatewayFactoryConfig(array $config)
    {
        $currentConfig = $this->coreGatewayFactoryConfig ?: array();
        $this->coreGatewayFactoryConfig = array_replace($currentConfig, $config);

        return $this;
    }

    /**
     * @param StorageInterface $gatewayConfigStorage
     *
     * @return static
     */
    public function setGatewayConfigStorage(StorageInterface $gatewayConfigStorage = null)
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;

        return $this;
    }

    /**
     * @param RegistryInterface $mainRegistry
     *
     * @return static
     */
    public function setMainRegistry(RegistryInterface $mainRegistry = null)
    {
        $this->mainRegistry = $mainRegistry;

        return $this;
    }

    /**
     * @param HttpClientInterface $httpClient
     *
     * @deprecated this method will be removed in 2.0 Use self::addCoreGatewayFactoryConfig to overwrite http client.
     *
     * @return static
     */
    public function setHttpClient(HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @return Payum
     */
    public function getPayum()
    {
        if (false == $tokenStorage = $this->tokenStorage) {
            throw new \LogicException('Token storage must be configured.');
        }

        $storages = $this->storages;

        $tokenFactory = $this->buildTokenFactory($this->tokenStorage, $this->buildRegistry(array(), $storages));
        $genericTokenFactory = $this->buildGenericTokenFactory($tokenFactory, array_replace(array(
            'capture' => $this->ConfigPath,
            'notify' => 'notify.php',
            'authorize' => 'authorize.php',
            'refund' => 'refund.php',
        ), $this->genericTokenFactoryPaths));

        $httpRequestVerifier = $this->buildHttpRequestVerifier($this->tokenStorage);

        if (false == $httpClient = $this->httpClient) {
            $httpClient = HttpClientFactory::create();
        }

        $coreGatewayFactory = $this->buildCoreGatewayFactory(array_replace(array(
            'payum.extension.token_factory' => new GenericTokenFactoryExtension($genericTokenFactory),
            'payum.security.token_storage' => $tokenStorage,
            'payum.http_client' => $httpClient,
        ), $this->coreGatewayFactoryConfig));

        $gatewayFactories = array_replace(
            $this->buildGatewayFactories($coreGatewayFactory),
            $this->buildOmnipayGatewayFactories($coreGatewayFactory),
            $this->gatewayFactories
        );

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

        return new Payum($registry, $httpRequestVerifier, $genericTokenFactory, $tokenStorage);
    }

    /**
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     *
     * @return TokenFactoryInterface
     */
    protected function buildTokenFactory(StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry)
    {
        $tokenFactory = $this->tokenFactory;

        if (is_callable($tokenFactory)) {
            $tokenFactory = call_user_func($tokenFactory, $tokenStorage, $storageRegistry);

            if (false == $tokenFactory instanceof TokenFactoryInterface) {
                throw new \LogicException('Builder returned invalid instance');
            }
        }

        return $tokenFactory ?: new TokenFactory($tokenStorage, $storageRegistry);
    }

    /**
     * @param TokenFactoryInterface $tokenFactory
     * @param string[]              $paths
     *
     * @return GenericTokenFactoryInterface
     *
     */
    protected function buildGenericTokenFactory(TokenFactoryInterface $tokenFactory, array $paths)
    {
        $genericTokenFactory = $this->genericTokenFactory;

        if (is_callable($genericTokenFactory)) {
            $genericTokenFactory = call_user_func($genericTokenFactory, $tokenFactory, $paths);

            if (false == $genericTokenFactory instanceof GenericTokenFactoryInterface) {
                throw new \LogicException('Builder returned invalid instance');
            }
        }

        return $genericTokenFactory ?: new GenericTokenFactory($tokenFactory, $paths);
    }

    /**
     * @param StorageInterface $tokenStorage
     *
     * @return HttpRequestVerifierInterface
     */
    private function buildHttpRequestVerifier(StorageInterface $tokenStorage)
    {
        $httpRequestVerifier = $this->httpRequestVerifier;

        if (is_callable($httpRequestVerifier)) {
            $httpRequestVerifier = call_user_func($httpRequestVerifier, $tokenStorage);

            if (false == $httpRequestVerifier instanceof HttpRequestVerifierInterface) {
                throw new \LogicException('Builder returned invalid instance');
            }
        }

        return $httpRequestVerifier ?: new HttpRequestVerifier($tokenStorage);
    }

    /**
     * @param array $config
     * @return GatewayFactoryInterface
     */
    private function buildCoreGatewayFactory(array $config)
    {
        $coreGatewayFactory = $this->coreGatewayFactory;

        $storages = $this->storages;
        foreach ($storages as $modelClass => $storage) {
            $extensionName = 'payum.extension.storage_'.strtolower(str_replace('\\', '_', $modelClass));

            $config[$extensionName] = new StorageExtension($storage);
        }

        if (is_callable($coreGatewayFactory)) {
            $coreGatewayFactory = call_user_func($coreGatewayFactory, $config);

            if (false == $coreGatewayFactory instanceof GatewayFactoryInterface) {
                throw new \LogicException('Builder returned invalid instance');
            }
        }

        return $coreGatewayFactory ?: new CoreGatewayFactory($config);
    }

    /**
     * @param array $gateways
     * @param array $storages
     * @param array $gatewayFactories
     *
     * @return RegistryInterface
     */
    protected function buildRegistry(array $gateways = array(), array $storages = array(), array $gatewayFactories = array())
    {
        $fallbackRegistry = new SimpleRegistry($gateways, $storages, $gatewayFactories);
        $fallbackRegistry->setAddStorageExtensions(false);

        if ($this->gatewayConfigStorage) {
            $fallbackRegistry = new DynamicRegistry($this->gatewayConfigStorage, $fallbackRegistry);
        }

        if ($this->mainRegistry) {
            $registry = new FallbackRegistry($this->mainRegistry, $fallbackRegistry);
        } else {
            $registry = $fallbackRegistry;
        }

        return $registry;
    }

    /**
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return GatewayFactoryInterface[]
     */
    protected function buildGatewayFactories(GatewayFactoryInterface $coreGatewayFactory)
    {
        $map =array(
            'paypal_express_checkout' => 'Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory',
            'paypal_pro_checkout' => 'Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory',
            'paypal_pro_hosted' => 'Payum\Paypal\ProHosted\Nvp\PaypalProHostedGatewayFactory',
            'paypal_masspay' => 'Payum\Paypal\Masspay\Nvp\PaypalMasspayGatewayFactory',
            'paypal_rest' => 'Payum\Paypal\Rest\PaypalRestGatewayFactory',
            'authorize_net_aim' => 'Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory',
            'be2bill_direct' => 'Payum\Be2Bill\Be2BillDirectGatewayFactory',
            'be2bill_offsite' => 'Payum\Be2Bill\Be2BillOffsiteGatewayFactory',
            'klarna_checkout' =>'Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory',
            'klarna_invoice' => 'Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory',
            'offline' =>  'Payum\Offline\OfflineGatewayFactory',
            'payex' =>  'Payum\Payex\PayexGatewayFactory',
            'stripe_checkout' => 'Payum\Stripe\StripeCheckoutGatewayFactory',
            'stripe_js' => 'Payum\Stripe\StripeJsGatewayFactory',
            'sofort' => 'Payum\Sofort\SofortGatewayFactory',
            'Tranzila' => 'Payum\Tranzila\TranzilaGatewayFactory',
            'Merchant e-Solutions' => 'Payum\MerchantESolution\MerchantESolutionGatewayFactory',
        );

        $gatewayFactories = array();

        foreach ($map as $name => $factoryClass) {
            if (class_exists($factoryClass)) {
                $gatewayFactories[$name] = new $factoryClass(
                    isset($this->gatewayFactoryConfigs[$name]) ? $this->gatewayFactoryConfigs[$name] : array(),
                    $coreGatewayFactory
                );
            }
        }

        return $gatewayFactories;
    }

    /**
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return GatewayFactoryInterface[]
     */
    protected function buildAddedGatewayFactories(GatewayFactoryInterface $coreGatewayFactory)
    {
        $gatewayFactories = array();
        foreach ($this->gatewayFactories as $name => $factory) {
            if (is_callable($factory)) {
                $config = isset($this->gatewayFactoryConfigs[$name]) ? $this->gatewayFactoryConfigs[$name] : array();

                $factory = call_user_func($factory, $config, $coreGatewayFactory);
            }

            $gatewayFactories[$name] = $factory;
        }

        return $gatewayFactories;
    }

    /**
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return GatewayFactoryInterface[]
     */
    protected function buildOmnipayGatewayFactories(GatewayFactoryInterface $coreGatewayFactory)
    {
        $gatewayFactories = array();
        if (false == class_exists('Omnipay\Omnipay')) {
            return $gatewayFactories;
        }

        $factory = \Omnipay\Omnipay::getFactory();

        $gatewayFactories['omnipay'] = new OmnipayGatewayFactory('', $factory, array(), $coreGatewayFactory);
        $gatewayFactories['omnipay_direct'] = new OmnipayGatewayFactory('', $factory, array(), $coreGatewayFactory);
        $gatewayFactories['omnipay_offsite'] = new OmnipayGatewayFactory('', $factory, array(), $coreGatewayFactory);

        foreach ($factory->getSupportedGateways() as $type) {
            // omnipay throws exception on these gateways https://github.com/thephpleague/omnipay/issues/312
            // skip them for now
            if (in_array($type, array('Buckaroo', 'Alipay Bank', 'AliPay Dual Func', 'Alipay Express', 'Alipay Mobile Express', 'Alipay Secured', 'Alipay Wap Express', 'Cybersource', 'DataCash', 'Ecopayz', 'Neteller', 'Pacnet', 'PaymentSense', 'Realex Remote', 'SecPay (PayPoint.net)', 'Sisow', 'Skrill', 'YandexMoney', 'YandexMoneyIndividual'))) {
                continue;
            }

            $gatewayFactories[strtolower('omnipay_'.$type)] = new OmnipayGatewayFactory($type, $factory, array(), $coreGatewayFactory);
        }

        return $gatewayFactories;
    }
}