<?php
namespace Payum\Core\Tests;

use Omnipay\Dummy\Gateway as OmnipayGateway;
use Payum\AuthorizeNet\Aim\AuthorizeNetAimGatewayFactory;
use Payum\Be2Bill\Be2BillDirectGatewayFactory;
use Payum\Be2Bill\Be2BillOffsiteGatewayFactory;
use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Payout;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;
use Payum\Offline\OfflineGatewayFactory;
use Payum\OmnipayBridge\OmnipayGatewayFactory;
use Payum\Payex\PayexGatewayFactory;
use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;
use Payum\Paypal\Masspay\Nvp\PaypalMasspayGatewayFactory;
use Payum\Paypal\ProCheckout\Nvp\PaypalProCheckoutGatewayFactory;
use Payum\Paypal\Rest\PaypalRestGatewayFactory;
use Payum\Stripe\StripeCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;

class PayumBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }


    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumBuilder();
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Token storage must be configured.
     */
    public function throwsIfTokenStorageIsNotSet()
    {
        $payum = (new PayumBuilder())->getPayum();

        $this->assertInstanceOf(Payum::class, $payum);
    }

    /**
     * @test
     */
    public function shouldBuildDefaultPayum()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertInstanceOf(HttpRequestVerifier::class, $payum->getHttpRequestVerifier());
        $this->assertInstanceOf(GenericTokenFactory::class, $payum->getTokenFactory());

        $this->assertInternalType('array', $payum->getGateways());
        $this->assertCount(0, $payum->getGateways());

        $this->assertInternalType('array', $payum->getStorages());
        $this->assertCount(3, $payum->getStorages());
        $this->assertArrayHasKey(Payment::class, $payum->getStorages());
        $this->assertArrayHasKey(ArrayObject::class, $payum->getStorages());
        $this->assertArrayHasKey(Payout::class, $payum->getStorages());


        $factories = $payum->getGatewayFactories();
        $this->assertInternalType('array', $factories);
        $this->assertGreaterThan(40, $factories);

        $this->assertArrayHasKey('paypal_express_checkout', $factories);
        $this->assertInstanceOf(PaypalExpressCheckoutGatewayFactory::class, $factories['paypal_express_checkout']);

        $this->assertArrayHasKey('paypal_masspay', $factories);
        $this->assertInstanceOf(PaypalMasspayGatewayFactory::class, $factories['paypal_masspay']);

        $this->assertArrayHasKey('paypal_pro_checkout', $factories);
        $this->assertInstanceOf(PaypalProCheckoutGatewayFactory::class, $factories['paypal_pro_checkout']);

        $this->assertArrayHasKey('paypal_rest', $factories);
        $this->assertInstanceOf(PaypalRestGatewayFactory::class, $factories['paypal_rest']);

        $this->assertArrayHasKey('authorize_net_aim', $factories);
        $this->assertInstanceOf(AuthorizeNetAimGatewayFactory::class, $factories['authorize_net_aim']);

        $this->assertArrayHasKey('be2bill_direct', $factories);
        $this->assertInstanceOf(Be2BillDirectGatewayFactory::class, $factories['be2bill_direct']);

        $this->assertArrayHasKey('be2bill_offsite', $factories);
        $this->assertInstanceOf(Be2BillOffsiteGatewayFactory::class, $factories['be2bill_offsite']);

        $this->assertArrayHasKey('klarna_checkout', $factories);
        $this->assertInstanceOf(KlarnaCheckoutGatewayFactory::class, $factories['klarna_checkout']);

        $this->assertArrayHasKey('klarna_invoice', $factories);
        $this->assertInstanceOf(KlarnaInvoiceGatewayFactory::class, $factories['klarna_invoice']);

        $this->assertArrayHasKey('offline', $factories);
        $this->assertInstanceOf(OfflineGatewayFactory::class, $factories['offline']);

        $this->assertArrayHasKey('payex', $factories);
        $this->assertInstanceOf(PayexGatewayFactory::class, $factories['payex']);

        $this->assertArrayHasKey('stripe_checkout', $factories);
        $this->assertInstanceOf(StripeCheckoutGatewayFactory::class, $factories['stripe_checkout']);

        $this->assertArrayHasKey('stripe_js', $factories);
        $this->assertInstanceOf(StripeJsGatewayFactory::class, $factories['stripe_js']);
    }

    /**
     * @test
     */
    public function shouldUseCustomHttpRequestVerifier()
    {
        /** @var HttpRequestVerifierInterface $expectedVerifier */
        $expectedVerifier = $this->getMock(HttpRequestVerifierInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setHttpRequestVerifier($expectedVerifier)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedVerifier, $payum->getHttpRequestVerifier());
    }

    /**
     * @test
     */
    public function shouldUseHttpRequestVerifierBuilder()
    {
        /** @var HttpRequestVerifierInterface $expectedVerifier */
        $expectedVerifier = $this->getMock(HttpRequestVerifierInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setHttpRequestVerifier(function ($tokenStorage) use ($expectedVerifier) {
                $this->assertInstanceOf(StorageInterface::class, $tokenStorage);

                return $expectedVerifier;
            })
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedVerifier, $payum->getHttpRequestVerifier());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Builder returned invalid instance
     */
    public function throwsIfHttpRequestVerifierBuilderReturnsInvalidInstance()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setHttpRequestVerifier(function () {
                return new \stdClass();
            })
            ->getPayum()
        ;
    }

    /**
     * @test
     */
    public function shouldUseCustomGenericTokenFactory()
    {
        /** @var GenericTokenFactoryInterface $expectedTokenFactory */
        $expectedTokenFactory = $this->getMock(GenericTokenFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setGenericTokenFactory($expectedTokenFactory)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedTokenFactory, $payum->getTokenFactory());
    }

    /**
     * @test
     */
    public function shouldUseGenericTokenFactoryBuilder()
    {
        /** @var GenericTokenFactoryInterface $expectedTokenFactory */
        $expectedTokenFactory = $this->getMock(GenericTokenFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setGenericTokenFactory(function ($tokenFactory, $paths) use ($expectedTokenFactory) {
                $this->assertInstanceOf(TokenFactoryInterface::class, $tokenFactory);

                $this->assertInternalType('array', $paths);
                $this->assertEquals([
                    'capture' => 'capture.php',
                    'notify' => 'notify.php',
                    'authorize' => 'authorize.php',
                    'refund' => 'refund.php',
                    'payout' => 'payout.php',
                ], $paths);

                return $expectedTokenFactory;
            })
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedTokenFactory, $payum->getTokenFactory());
    }

    /**
     * @test
     */
    public function shouldUseCustomGenericTokenFactoryPaths()
    {
        $expectedPaths = [
            'capture' => 'capture_custom.php',
            'notify' => 'notify_custom.php',
            'authorize' => 'authorize_custom.php',
            'refund' => 'refund_custom.php',
            'payout' => 'payout_custom.php',
        ];

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setGenericTokenFactoryPaths($expectedPaths)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertAttributeSame($expectedPaths, 'paths', $payum->getTokenFactory());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Builder returned invalid instance
     */
    public function throwsIfGenericTokenFactoryBuilderReturnInvalidInstance()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setGenericTokenFactory(function () {
                return new \stdClass();
            })
            ->getPayum()
        ;
    }

    /**
     * @test
     */
    public function shouldUseCustomTokenFactory()
    {
        /** @var TokenFactoryInterface $expectedTokenFactory */
        $expectedTokenFactory = $this->getMock(TokenFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setTokenFactory($expectedTokenFactory)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertAttributeSame($expectedTokenFactory, 'tokenFactory', $payum->getTokenFactory());
    }

    /**
     * @test
     */
    public function shouldUseTokenFactoryBuilder()
    {
        /** @var TokenFactoryInterface $expectedTokenFactory */
        $expectedTokenFactory = $this->getMock(TokenFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setTokenFactory(function ($tokenStorage, $storageRegistry) use ($expectedTokenFactory) {
                $this->assertInstanceOf(StorageInterface::class, $tokenStorage);
                $this->assertInstanceOf(StorageRegistryInterface::class, $storageRegistry);

                return $expectedTokenFactory;
            })
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertAttributeSame($expectedTokenFactory, 'tokenFactory', $payum->getTokenFactory());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Builder returned invalid instance
     */
    public function throwsIfTokenFactoryBuilderReturnInvalidInstance()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setTokenFactory(function () {
                return new \stdClass();
            })
            ->getPayum()
        ;
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayAddedAsInstance()
    {
        $expectedGateway = new Gateway();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGateway('a_gateway', $expectedGateway)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedGateway, $payum->getGateway('a_gateway'));
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayAddedAsConfig()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGateway('a_gateway', [
                'factory' => 'offline'
            ])
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertInstanceOf(Gateway::class, $payum->getGateway('a_gateway'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage Gateway config must have factory set in it and it must not be empty.
     */
    public function throwIfTryToAddGatewayConfigWithoutFactoryKeySet()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGateway('a_gateway', [
            ])
            ->getPayum()
        ;
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageAddedAsInstance()
    {
        /** @var StorageInterface $expectedStorage */
        $expectedStorage = $this->getMock(StorageInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addStorage('a_storage', $expectedStorage)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedStorage, $payum->getStorage('a_storage'));
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayFactoryAddedAsInstance()
    {
        /** @var GatewayFactoryInterface $expectedFactory */
        $expectedFactory = $this->getMock(GatewayFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('a_factory', $expectedFactory)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedFactory, $payum->getGatewayFactory('a_factory'));
    }

    /**
     * @test
     */
    public function shouldAllowGetGatewayFactoryAddedAsCallbackFactory()
    {
        $expectedFactory = null;

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('a_factory', function (array $config, GatewayFactoryInterface $coreGatewayFactory) use (&$expectedFactory) {
                $expectedFactory = new GatewayFactory($config, $coreGatewayFactory);

                return $expectedFactory;
            })
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($expectedFactory, $payum->getGatewayFactory('a_factory'));
    }

    /**
     * @test
     */
    public function shouldPassAddedConfigToGatewayCallbackFactory()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactoryConfig('a_factory', ['foo' => 'fooVal'])
            ->addGatewayFactory('a_factory', function (array $config, GatewayFactoryInterface $coreGatewayFactory) use (&$expectedFactory) {
                $this->assertEquals(['foo' => 'fooVal'], $config);

                return new GatewayFactory($config, $coreGatewayFactory);
            })
            ->getPayum()
        ;
    }

    /**
     * @test
     */
    public function shouldReuseAddedFactoriesForGatewayCreatedFromConfig()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('a_factory', new OfflineGatewayFactory())
            ->addGateway('a_gateway', [
                'factory' => 'a_factory',
            ])
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertInstanceOf(Gateway::class, $payum->getGateway('a_gateway'));
    }

    /**
     * @test
     */
    public function shouldReuseGatewaysFromMainRegistryAndFallbackOne()
    {
        $fallbackGateway = new Gateway();
        $mainGateway = new Gateway();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGateway('fallback_factory', $fallbackGateway)
            ->setMainRegistry(new SimpleRegistry([
                'main_gateway' => $mainGateway
            ]))
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($mainGateway, $payum->getGateway('main_gateway'));
        $this->assertSame($fallbackGateway, $payum->getGateway('fallback_factory'));
    }

    /**
     * @test
     */
    public function shouldAllowSetReuseGatewaysFromMainRegistryAndFallbackOne()
    {
        $fallbackGateway = new Gateway();
        $mainGateway = new Gateway();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGateway('fallback_factory', $fallbackGateway)
            ->setMainRegistry(new SimpleRegistry([
                'main_gateway' => $mainGateway
            ]))
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($mainGateway, $payum->getGateway('main_gateway'));
        $this->assertSame($fallbackGateway, $payum->getGateway('fallback_factory'));
    }

    /**
     * @test
     */
    public function shouldUseCustomCoreGatewayFactory()
    {
        $expectedCoreGatewayFactory = $this->getMock(GatewayFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setCoreGatewayFactory($expectedCoreGatewayFactory)
            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('offline');

        $this->assertInstanceOf(OfflineGatewayFactory::class, $gatewayFactory);

        $this->assertAttributeSame($expectedCoreGatewayFactory, 'coreGatewayFactory', $gatewayFactory);
    }

    /**
     * @test
     */
    public function shouldUseCoreGatewayFactoryBuilder()
    {
        $expectedCoreGatewayFactory = $this->getMock(GatewayFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setCoreGatewayFactory(function ($config) use ($expectedCoreGatewayFactory) {
                $this->assertInternalType('array', $config);
                $this->assertNotEmpty($config);

                return $expectedCoreGatewayFactory;
            })
            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('offline');

        $this->assertInstanceOf(OfflineGatewayFactory::class, $gatewayFactory);

        $this->assertAttributeSame($expectedCoreGatewayFactory, 'coreGatewayFactory', $gatewayFactory);
    }

    /**
     * @test
     */
    public function shouldAddStorageExtensionForTheAddedStorage()
    {
        /** @var StorageInterface $expectedStorage */
        $expectedStorage = $this->getMock(StorageInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addStorage(TestModel::class, $expectedStorage)
            ->setCoreGatewayFactory(function ($config) use ($expectedStorage) {
                $this->assertInternalType('array', $config);
                $this->assertArrayHasKey('payum.extension.storage_payum_core_tests_testmodel', $config, var_export($config, true));
                $this->assertInstanceOf(StorageExtension::class, $config['payum.extension.storage_payum_core_tests_testmodel']);
                $this->assertAttributeSame($expectedStorage, 'storage', $config['payum.extension.storage_payum_core_tests_testmodel']);

                return new CoreGatewayFactory($config);
            })
            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('offline');

        $this->assertInstanceOf(OfflineGatewayFactory::class, $gatewayFactory);

        $config = $gatewayFactory->createConfig([]);

        $this->assertArrayHasKey('payum.extension.storage_payum_core_tests_testmodel', $config, var_export($config, true));
        $this->assertInstanceOf(StorageExtension::class, $config['payum.extension.storage_payum_core_tests_testmodel']);
        $this->assertAttributeSame($expectedStorage, 'storage', $config['payum.extension.storage_payum_core_tests_testmodel']);
    }

    /**
     * @test
     */
    public function shouldAllowAddGatewayFactorySpecificConfig()
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactoryConfig('offline', ['foo' => 'fooVal'])

            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('offline');

        $this->assertInstanceOf(OfflineGatewayFactory::class, $gatewayFactory);

        $config = $gatewayFactory->createConfig([]);

        $this->assertArrayHasKey('foo', $config, var_export($config, true));
        $this->assertEquals('fooVal', $config['foo']);
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Builder returned invalid instance
     */
    public function throwsIfCoreGatewayFactoryBuilderReturnInvalidInstance()
    {
        $expectedCoreGateway = $this->getMock(GatewayFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setCoreGatewayFactory(function () {
                return new \stdClass();
            })
            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('offline');

        $this->assertInstanceOf(OfflineGatewayFactory::class, $gatewayFactory);

        $this->assertAttributeSame($expectedCoreGateway, 'coreGatewayFactory', $gatewayFactory);
    }

    /**
     * @test
     */
    public function shouldRegisterOmnipayFactories()
    {
        if (false == class_exists(OmnipayGateway::class)) {
            $this->markTestSkipped('Either omnipay or\and omnipay bridge are not installed. Skip');
        }

        $expectedCoreGatewayFactory = $this->getMock(GatewayFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setCoreGatewayFactory($expectedCoreGatewayFactory)
            ->getPayum()
        ;

        $gatewayFactories = $payum->getGatewayFactories();

        $this->assertArrayHasKey('omnipay_dummy', $gatewayFactories);
        $this->assertArrayHasKey('omnipay_stripe', $gatewayFactories);
        $this->assertArrayHasKey('omnipay_paypal_express', $gatewayFactories);
    }

    /**
     * @test
     */
    public function shouldInjectCoreGatewayFactoryToOmnipayFactory()
    {
        if (false == class_exists(OmnipayGateway::class)) {
            $this->markTestSkipped('Either omnipay or\and omnipay bridge are not installed. Skip');
        }

        $expectedCoreGatewayFactory = $this->getMock(GatewayFactoryInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setCoreGatewayFactory($expectedCoreGatewayFactory)
            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('omnipay_dummy');

        $this->assertInstanceOf(OmnipayGatewayFactory::class, $gatewayFactory);

        $this->assertAttributeSame($expectedCoreGatewayFactory, 'coreGatewayFactory', $gatewayFactory);
    }

    /**
     * @test
     */
    public function shouldInjectExpectedOmnipayGatewayInstanceAsApi()
    {
        if (false == class_exists(OmnipayGateway::class)) {
            $this->markTestSkipped('Either omnipay or\and omnipay bridge are not installed. Skip');
        }

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->getPayum()
        ;

        $gatewayFactory = $payum->getGatewayFactory('omnipay_dummy');

        $this->assertInstanceOf(OmnipayGatewayFactory::class, $gatewayFactory);

        $gateway = $gatewayFactory->create();

        $apis = $this->readAttribute($gateway, 'apis');

        $this->assertCount(2, $apis);
        $this->assertInstanceOf(OmnipayGateway::class, $apis[1]);
        $this->assertEquals('Dummy', $apis[1]->getName());
    }

    /**
     * @test
     */
    public function shouldAddTokenStorageToCoreGatewayConfig()
    {
        $tokenStorageMock = $this->getMock(StorageInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->setTokenStorage($tokenStorageMock)
            ->setCoreGatewayFactory(function ($config) use ($tokenStorageMock) {
                $this->assertInternalType('array', $config);
                $this->assertArrayHasKey('payum.security.token_storage', $config);
                $this->assertSame($tokenStorageMock, $config['payum.security.token_storage']);

                return new CoreGatewayFactory();
            })
            ->getPayum()
        ;
    }

    /**
     * @test
     */
    public function shouldAllowAddCoreGatewayConfig()
    {
        $payumBuilder = new PayumBuilder();

        $payumBuilder->setCoreGatewayFactoryConfig(['foo' => 'fooVal', 'bar' => 'barVal']);
        $this->assertAttributeSame(['foo' => 'fooVal', 'bar' => 'barVal'], 'coreGatewayFactoryConfig', $payumBuilder);

        $payumBuilder->addCoreGatewayFactoryConfig(['baz' => 'bazVal', 'foo' => 'fooNewVal']);

        $this->assertAttributeSame(['foo' => 'fooNewVal', 'bar' => 'barVal', 'baz' => 'bazVal'], 'coreGatewayFactoryConfig', $payumBuilder);
    }

    /**
     * @test
     */
    public function shouldAllowAddGatewayConfigSeveralTimes()
    {
        $payumBuilder = new PayumBuilder();

        $payumBuilder->addGateway('foo', ['factory' => 'aFactory', 'foo' => 'fooVal', 'bar' => 'barVal']);
        $this->assertAttributeSame(['foo' => ['factory' => 'aFactory', 'foo' => 'fooVal', 'bar' => 'barVal']], 'gatewayConfigs', $payumBuilder);

        $payumBuilder->addGateway('foo', ['baz' => 'bazVal', 'foo' => 'fooNewVal']);

        $this->assertAttributeSame(['foo' => ['factory' => 'aFactory', 'foo' => 'fooNewVal', 'bar' => 'barVal', 'baz' => 'bazVal']], 'gatewayConfigs', $payumBuilder);
    }

    /**
     * @test
     */
    public function shouldAllowAddGatewayFactoryConfigSeveralTimes()
    {
        $payumBuilder = new PayumBuilder();

        $payumBuilder->addGatewayFactoryConfig('foo', ['foo' => 'fooVal', 'bar' => 'barVal']);
        $this->assertAttributeSame(['foo' => ['foo' => 'fooVal', 'bar' => 'barVal']], 'gatewayFactoryConfigs', $payumBuilder);

        $payumBuilder->addGatewayFactoryConfig('foo', ['baz' => 'bazVal', 'foo' => 'fooNewVal']);

        $this->assertAttributeSame(['foo' => ['foo' => 'fooNewVal', 'bar' => 'barVal', 'baz' => 'bazVal']], 'gatewayFactoryConfigs', $payumBuilder);
    }

    /**
     * @test
     */
    public function shouldAllowBuildGatewayWithCoreGatewayFactory()
    {
        $payumBuilder = new PayumBuilder();

        $payum = $payumBuilder
            ->addDefaultStorages()
            ->addGateway('foo', ['factory' => 'core'])

            ->getPayum()
        ;

        $gateway = $payum->getGateway('foo');

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->getMock(RegistryInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpRequestVerifierInterface
     */
    protected function createHttpRequestVerifierMock()
    {
        return $this->getMock(HttpRequestVerifierInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->getMock(GenericTokenFactoryInterface::class);
    }
}

class TestModel
{
}
