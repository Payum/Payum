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
use Payum\Core\Model\Token;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\FallbackRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Storage\StorageInterface;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;
use Payum\Offline\OfflineGatewayFactory;
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
     * @var GenericTokenFactory
     */
    protected $tokenFactory;

    /**
     * @var StorageInterface
     */
    protected $tokenStorage;

    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

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
     * @param string $name
     * @param array  $gatewayConfig
     *
     * @return static
     */
    public function addGatewayConfig($name, array $gatewayConfig)
    {


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
        $fallbackRegistry = new SimpleRegistry($this->gateways, $this->storages);
        if (false == $this->gatewayConfigStorage) {
            $fallbackRegistry = new DynamicRegistry($this->gatewayConfigStorage, $fallbackRegistry);
        }

        if (false == $tokenStorage = $this->tokenStorage) {
            $tokenStorage = new FilesystemStorage(sys_get_temp_dir().'/payum', Token::class, 'hash');
        }

        if (false == $tokenFactory = $this->tokenFactory) {
            $tokenFactory = new GenericTokenFactory(new TokenFactory($tokenStorage, $fallbackRegistry), [
                'capture' => 'capture.php',
                'notify' => 'notify.php',
                'authorize' => 'authorize.php',
                'refund' => 'refund.php',
            ]);
        }

        if (false == $httpRequestVerifier = $this->httpRequestVerifier) {
            $httpRequestVerifier = new HttpRequestVerifier($this->tokenStorage);
        }

        if (false == $httpClient = $this->httpClient) {
            $httpClient = HttpClientFactory::create();
        }

        if (false == $coreGatewayFactory = $this->coreGatewayFactory) {
            $coreGatewayFactory = new CoreGatewayFactory(array_replace([
                'payum.extension.token_factory' => new GenericTokenFactoryExtension($tokenFactory),
                'payum.http_client' => $httpClient,

            ], $this->coreGatewayFactoryConfig));
        }

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

        // again with gateway factories
        $fallbackRegistry = new SimpleRegistry($this->gateways, $this->storages, array_replace($defaultGatewayFactories, $gatewayFactories));
        if (false == $this->gatewayConfigStorage) {
            $fallbackRegistry = new DynamicRegistry($this->gatewayConfigStorage, $fallbackRegistry);
        }

        if ($this->mainRegistry) {
            $registry = new FallbackRegistry($this->mainRegistry, $fallbackRegistry);
        } else {
            $registry = $fallbackRegistry;
        }

        if ($this->gatewayConfigs) {
            $gateways = $this->gateways;
            foreach ($this->gatewayConfigs as $name => $gatewayConfig) {
                $gatewayFactory = $fallbackRegistry->getGatewayFactory($gatewayConfig['factory']);
                unset($gatewayConfig['factory']);

                $gateways[$name] = $gatewayFactory->create($gatewayConfig);
            }

            // again with gateways created from configs
            $fallbackRegistry = new SimpleRegistry($this->gateways, $this->storages, array_replace($defaultGatewayFactories, $gatewayFactories));

            if ($this->mainRegistry) {
                $registry = new FallbackRegistry($this->mainRegistry, $fallbackRegistry);
            } else {
                $registry = $fallbackRegistry;
            }
        }

        return new Payum($registry, $httpRequestVerifier, $tokenFactory);
    }

    /**
     * @param HttpRequestVerifierInterface $httpRequestVerifier
     *
     * @return static
     */
    public function setHttpRequestVerifier(HttpRequestVerifierInterface $httpRequestVerifier)
    {
        $this->httpRequestVerifier = $httpRequestVerifier;

        return $this;
    }

    /**
     * @param GenericTokenFactory $tokenFactory
     *
     * @return static
     */
    public function setTokenFactory(GenericTokenFactory $tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;

        return $this;
    }

    /**
     * @param StorageInterface $tokenStorage
     *
     * @return static
     */
    public function setTokenStorage(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return static
     */
    public function setCoreGatewayFactory(GatewayFactoryInterface $coreGatewayFactory)
    {
        $this->coreGatewayFactory = $coreGatewayFactory;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return static
     */
    public function setCoreGatewayFactoryConfig(array $config)
    {
        $this->coreGatewayFactoryConfig = $config;

        return $this;
    }

    /**
     * @param StorageInterface $gatewayConfigStorage
     *
     * @return static
     */
    public function setGatewayConfigStorage(StorageInterface $gatewayConfigStorage)
    {
        $this->gatewayConfigStorage = $gatewayConfigStorage;

        return $this;
    }

    /**
     * @param HttpClientInterface $httpClient
     *
     * @return static
     */
    public function setHttpClient(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * @param RegistryInterface $mainRegistry
     *
     * @return PayumBuilder
     */
    public function setMainRegistry(RegistryInterface $mainRegistry)
    {
        $this->mainRegistry = $mainRegistry;

        return $this;
    }
}