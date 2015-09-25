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
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Token;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\FallbackRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;
use Payum\Offline\OfflineGatewayFactory;
use Payum\OmnipayBridge\OmnipayDirectGatewayFactory;
use Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory;
use Payum\Payex\PayexGatewayFactory;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory;
use Payum\Paypal\Rest\PaypalRestGatewayFactory;
use Payum\Stripe\StripeCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;

class PayumBuilder
{
    /**
     * @var HttpRequestVerifierInterface
     */
    protected $httpRequestVerifier;

    /**
     * @var callable
     */
    protected $httpRequestVerifierBuilder;

    /**
     * @var TokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @var callable
     */
    protected $tokenFactoryBuilder;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $genericTokenFactory;

    /**
     * @var string[]
     */
    protected $genericTokenFactoryPaths = [];

    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var callable
     */
    protected $coreGatewayFactoryBuilder;

    /**
     * @var array
     */
    protected $coreGatewayFactoryConfig = [];

    /**
     * @var StorageInterface
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
     * @var GatewayFactoryInterface[]
     */
    protected $gatewayFactories = [];

    /**
     * @var StorageInterface[]
     */
    protected $storages = [];

    /**
     * @var RegistryInterface
     */
    protected $mainRegistry;

    /**
     * @var HttpClientInterface
     */
    protected $httpClient;

    /**
     * @return static
     */
    public function addDefaultStorages()
    {
        $this
            ->setTokenStorage(new FilesystemStorage(sys_get_temp_dir(), Token::class, 'hash'))
            ->addStorage(Payment::class, new FilesystemStorage(sys_get_temp_dir(), Payment::class, 'number'))
            ->addStorage(ArrayObject::class, new FilesystemStorage(sys_get_temp_dir(), ArrayObject::class))
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
                throw new InvalidArgumentException('Gateway config must have factory key and it must not be empty.');
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
     * @return Payum
     */
    public function getPayum()
    {
        if (false == $tokenStorage = $this->tokenStorage) {
            throw new \LogicException('Token storage must be configured.');
        }

        $genericTokenFactory = $this->buildGenericTokenFactory();

        $httpRequestVerifier = $this->buildHttpRequestVerifier();

        if (false == $httpClient = $this->httpClient) {
            $httpClient = HttpClientFactory::create();
        }

        $coreGatewayFactory = $this->buildCoreGatewayFactory(array_replace([
            'payum.extension.token_factory' => new GenericTokenFactoryExtension($genericTokenFactory),
            'payum.http_client' => $httpClient,
        ], $this->coreGatewayFactoryConfig));

        $gatewayFactories = $this->buildGatewayFactories($coreGatewayFactory);

        $registry = $this->buildRegistry($this->gateways, $this->storages, $gatewayFactories);

        if ($this->gatewayConfigs) {
            $gateways = $this->gateways;
            foreach ($this->gatewayConfigs as $name => $gatewayConfig) {
                $gatewayFactory = $registry->getGatewayFactory($gatewayConfig['factory']);
                unset($gatewayConfig['factory']);

                $gateways[$name] = $gatewayFactory->create($gatewayConfig);
            }

            $registry = $this->buildRegistry($gateways, $this->storages, $gatewayFactories);
        }

        return new Payum($registry, $httpRequestVerifier, $genericTokenFactory);
    }

    /**
     * @return GenericTokenFactoryInterface
     */
    protected function buildGenericTokenFactory()
    {
        if ($this->genericTokenFactory) {
            return $this->genericTokenFactory;
        }

        $tokenStorage = $this->tokenStorage;
        $storageRegistry = $this->buildRegistry([], $this->storages);

        if ($this->tokenFactoryBuilder) {
            $tokenFactory = call_user_func($this->tokenFactoryBuilder, $tokenStorage, $storageRegistry);
        } else if ($this->tokenFactory) {
            $tokenFactory = $this->tokenFactory;
        } else {
            $tokenFactory = new TokenFactory($tokenStorage, $storageRegistry);
        }

        return new GenericTokenFactory($tokenFactory, $this->genericTokenFactoryPaths ?: [
            'capture' => 'capture.php',
            'notify' => 'notify.php',
            'authorize' => 'authorize.php',
            'refund' => 'refund.php',
        ]);
    }

    /**
     * @param array $gateways
     * @param array $storages
     * @param array $gatewayFactories
     *
     * @return RegistryInterface
     */
    protected function buildRegistry(array $gateways = [], array $storages = [], array $gatewayFactories = [])
    {
        $fallbackRegistry = new SimpleRegistry($gateways, $storages, $gatewayFactories);
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
     * @return HttpRequestVerifierInterface
     */
    private function buildHttpRequestVerifier()
    {
        if ($this->httpRequestVerifier) {
            return $this->httpRequestVerifier;
        }

        if ($this->httpRequestVerifierBuilder) {
            return call_user_func($this->httpRequestVerifierBuilder, $this->tokenStorage);
        }

        return new HttpRequestVerifier($this->tokenStorage);
    }

    /**
     * @param array $defaultConfig
     *
     * @return GatewayFactoryInterface
     */
    private function buildCoreGatewayFactory(array $defaultConfig)
    {
        if ($this->coreGatewayFactory) {
            return $this->coreGatewayFactory;
        }

        if ($this->coreGatewayFactoryBuilder) {
            return call_user_func($this->coreGatewayFactoryBuilder, $defaultConfig);
        }

        return new CoreGatewayFactory($defaultConfig);
    }

    /**
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return GatewayFactoryInterface[]
     */
    protected function buildGatewayFactories(GatewayFactoryInterface $coreGatewayFactory)
    {
        $gatewayFactories = $this->gatewayFactories;
        $defaultGatewayFactories = [];

        if (class_exists(PaypalExpressCheckoutGatewayFactory::class)) {
            $defaultGatewayFactories['paypal_express_checkout'] = new PaypalExpressCheckoutGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(PaypalProCheckoutGatewayFactory::class)) {
            $defaultGatewayFactories['paypal_pro_checkout'] = new PaypalProCheckoutGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(PaypalRestGatewayFactory::class)) {
            $defaultGatewayFactories['paypal_rest'] = new PaypalRestGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(AuthorizeNetAimGatewayFactory::class)) {
            $defaultGatewayFactories['authorize_net_aim'] = new AuthorizeNetAimGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(Be2BillDirectGatewayFactory::class)) {
            $defaultGatewayFactories['be2bill_direct'] = new Be2BillDirectGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(Be2BillOffsiteGatewayFactory::class)) {
            $defaultGatewayFactories['be2bill_offsite'] = new Be2BillOffsiteGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(KlarnaCheckoutGatewayFactory::class)) {
            $defaultGatewayFactories['klarna_checkout'] = new KlarnaCheckoutGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(KlarnaInvoiceGatewayFactory::class)) {
            $defaultGatewayFactories['klarna_invoice'] = new KlarnaInvoiceGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(OfflineGatewayFactory::class)) {
            $defaultGatewayFactories['offline'] = new OfflineGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(PayexGatewayFactory::class)) {
            $defaultGatewayFactories['payex'] = new PayexGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(StripeCheckoutGatewayFactory::class)) {
            $defaultGatewayFactories['stripe_checkout'] = new StripeCheckoutGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(StripeJsGatewayFactory::class)) {
            $defaultGatewayFactories['stripe_js'] = new StripeJsGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(OmnipayDirectGatewayFactory::class)) {
            $defaultGatewayFactories['omnipay_direct'] = new OmnipayDirectGatewayFactory([], $coreGatewayFactory);
        }
        if (class_exists(OmnipayOffsiteGatewayFactory::class)) {
            $defaultGatewayFactories['omnipay_offsite'] = new OmnipayOffsiteGatewayFactory([], $coreGatewayFactory);
        }

        return array_replace($defaultGatewayFactories, $gatewayFactories);
    }

    /**
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     *
     * @return static
     */
    public function setHttpRequestVerifier(HttpRequestVerifierInterface $httpRequestVerifier = null)
    {
        $this->httpRequestVerifier = $httpRequestVerifier;

        return $this;
    }

    /**
     * @param TokenFactoryInterface $tokenFactory
     *
     * @return static
     */
    public function setTokenFactory(TokenFactoryInterface $tokenFactory = null)
    {
        $this->tokenFactory = $tokenFactory;

        return $this;
    }

    /**
     * @param callable $builder
     *
     * @return static
     */
    public function setTokenFactoryBuilder(callable $builder = null)
    {
        $this->tokenFactoryBuilder = $builder;

        return $this;
    }

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     *
     * @return static
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $tokenFactory = null)
    {
        $this->genericTokenFactory = $tokenFactory;

        return $this;
    }

    /**
     * @param \string[] $paths
     *
     * @return static
     */
    public function setGenericTokenFactoryPaths(array $paths = [])
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
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return static
     */
    public function setCoreGatewayFactory(GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory;

        return $this;
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
     * @param HttpClientInterface $httpClient
     *
     * @return static
     */
    public function setHttpClient(HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient;

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
     * @param callable $builder
     *
     * @return static
     */
    public function setHttpRequestVerifierBuilder(callable $builder = null)
    {
        $this->httpRequestVerifierBuilder = $builder;

        return $this;
    }

    /**
     * @param callable $builder
     *
     * @return static
     */
    public function setCoreGatewayFactoryBuilder(callable $builder = null)
    {
        $this->coreGatewayFactoryBuilder = $builder;

        return $this;
    }
}