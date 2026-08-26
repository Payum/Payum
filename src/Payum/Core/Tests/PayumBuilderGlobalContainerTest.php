<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use DI\Container;
use DI\ContainerBuilder;
use InvalidArgumentException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\PlainPhp\Security\HttpRequestVerifier;
use Payum\Core\Clock\SystemClock;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\DI\CreatesGateway;
use Payum\Core\DI\ListableContainerInterface;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Model\ArrayObject;
use Payum\Core\Model\Payment;
use Payum\Core\Model\Payout;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\Mocks\Clock\FrozenClock;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ReflectionProperty;
use stdClass;
use function DI\autowire;
use function DI\get;

final class PayumBuilderGlobalContainerTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'payum.dev',
        ];
    }

    public function testAddGlobalServiceShouldBeFluent(): void
    {
        $builder = new PayumBuilder();

        $this->assertSame($builder, $builder->addGlobalService('foo', 'fooVal'));
    }

    public function testAddGlobalServiceShouldStoreTheDefinition(): void
    {
        $builder = new PayumBuilder();

        $service = new stdClass();

        $builder->addGlobalService('foo', 'fooVal');
        $builder->addGlobalService(stdClass::class, $service);

        $ref = new ReflectionProperty($builder, 'globalDefinitions');

        $this->assertSame([
            'foo' => 'fooVal',
            stdClass::class => $service,
        ], $ref->getValue($builder));
    }

    public function testAddGlobalServiceShouldOverridePreviouslyAddedServiceWithSameId(): void
    {
        $builder = new PayumBuilder();

        $builder->addGlobalService('foo', 'fooVal');
        $builder->addGlobalService('foo', 'fooNewVal');

        $ref = new ReflectionProperty($builder, 'globalDefinitions');

        $this->assertSame([
            'foo' => 'fooNewVal',
        ], $ref->getValue($builder));
    }

    public function testSetGlobalContainerShouldBeFluent(): void
    {
        $builder = new PayumBuilder();

        $this->assertSame($builder, $builder->setGlobalContainer(new Container()));
    }

    public function testSetGlobalContainerShouldStoreTheContainer(): void
    {
        $builder = new PayumBuilder();

        $container = new Container();

        $builder->setGlobalContainer($container);

        $ref = new ReflectionProperty($builder, 'globalContainer');

        $this->assertSame($container, $ref->getValue($builder));
    }

    public function testBuildGlobalContainerShouldProvideTheSecurityServices(): void
    {
        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->buildGlobalContainer()
        ;

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertInstanceOf(HttpRequestVerifierInterface::class, $container->get(HttpRequestVerifierInterface::class));
        $this->assertInstanceOf(GenericTokenFactoryInterface::class, $container->get(GenericTokenFactoryInterface::class));
        $this->assertInstanceOf(TokenFactoryInterface::class, $container->get(TokenFactoryInterface::class));
        $this->assertInstanceOf(StorageInterface::class, $container->get('payum.security.token_storage'));
    }

    public function testBuildGlobalContainerShouldProvideThePsrHttpServices(): void
    {
        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->buildGlobalContainer()
        ;

        $this->assertInstanceOf(ClientInterface::class, $container->get(ClientInterface::class));
        $this->assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactoryInterface::class));
        $this->assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactoryInterface::class));
    }

    public function testBuildGlobalContainerShouldShareTheSameHttpServiceInstanceBetweenCalls(): void
    {
        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->buildGlobalContainer()
        ;

        $this->assertSame($container->get(ClientInterface::class), $container->get(ClientInterface::class));
        $this->assertSame($container->get(StreamFactoryInterface::class), $container->get(StreamFactoryInterface::class));
        $this->assertSame($container->get(RequestFactoryInterface::class), $container->get(RequestFactoryInterface::class));
    }

    public function testBuildGlobalContainerShouldProvideASystemClockByDefault(): void
    {
        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->buildGlobalContainer()
        ;

        $this->assertInstanceOf(SystemClock::class, $container->get(ClockInterface::class));
        $this->assertSame($container->get(ClockInterface::class), $container->get(ClockInterface::class));
    }

    public function testBuildGlobalContainerShouldLetTheApplicationReplaceTheClock(): void
    {
        $clock = new FrozenClock('2026-01-01 12:00:00');

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ClockInterface::class, $clock)
            ->buildGlobalContainer()
        ;

        $this->assertSame($clock, $container->get(ClockInterface::class));
    }

    public function testBuildGlobalContainerShouldAddDefaultStoragesWhenNoTokenStorageIsSet(): void
    {
        $builder = new ExposedGlobalContainerPayumBuilder();

        $container = $builder->buildGlobalContainer();

        $this->assertInstanceOf(StorageInterface::class, $container->get('payum.security.token_storage'));

        $ref = new ReflectionProperty($builder, 'storages');

        $this->assertArrayHasKey(Payment::class, $ref->getValue($builder));
        $this->assertArrayHasKey(ArrayObject::class, $ref->getValue($builder));
        $this->assertArrayHasKey(Payout::class, $ref->getValue($builder));
    }

    public function testBuildGlobalContainerShouldUseTheConfiguredTokenStorage(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->setTokenStorage($tokenStorage)
            ->buildGlobalContainer()
        ;

        $this->assertSame($tokenStorage, $container->get('payum.security.token_storage'));
    }

    public function testBuildGlobalContainerShouldUseTheConfiguredTokenFactoryAndVerifier(): void
    {
        $tokenFactory = $this->createMock(TokenFactoryInterface::class);
        $genericTokenFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $httpRequestVerifier = $this->createMock(HttpRequestVerifierInterface::class);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->setTokenFactory($tokenFactory)
            ->setGenericTokenFactory($genericTokenFactory)
            ->setHttpRequestVerifier($httpRequestVerifier)
            ->buildGlobalContainer()
        ;

        $this->assertSame($tokenFactory, $container->get(TokenFactoryInterface::class));
        $this->assertSame($genericTokenFactory, $container->get(GenericTokenFactoryInterface::class));
        $this->assertSame($httpRequestVerifier, $container->get(HttpRequestVerifierInterface::class));
    }

    public function testBuildGlobalContainerShouldRegisterAStorageExtensionForEveryStorage(): void
    {
        $storage = $this->createMock(StorageInterface::class);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->addStorage(GlobalContainerTestModel::class, $storage)
            ->buildGlobalContainer()
        ;

        foreach ([
            'payum.extension.storage_payum_core_model_payment',
            'payum.extension.storage_payum_core_model_arrayobject',
            'payum.extension.storage_payum_core_model_payout',
            'payum.extension.storage_payum_core_tests_globalcontainertestmodel',
        ] as $extensionName) {
            $this->assertTrue($container->has($extensionName));
            $this->assertInstanceOf(StorageExtension::class, $container->get($extensionName));
        }

        $extension = $container->get('payum.extension.storage_payum_core_tests_globalcontainertestmodel');

        $this->assertSame($storage, $this->readAttribute($extension, 'storage'));
    }

    public function testBuildGlobalContainerShouldExposeServicesAddedWithAddGlobalService(): void
    {
        $service = new stdClass();

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService('acme.service', $service)
            ->addGlobalService('acme.value', 'aValue')
            ->buildGlobalContainer()
        ;

        $this->assertTrue($container->has('acme.service'));
        $this->assertSame($service, $container->get('acme.service'));
        $this->assertSame('aValue', $container->get('acme.value'));
    }

    public function testBuildGlobalContainerShouldResolveCallableGlobalServices(): void
    {
        $service = new stdClass();

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService('acme.service', static fn (): stdClass => $service)
            ->buildGlobalContainer()
        ;

        $this->assertSame($service, $container->get('acme.service'));
    }

    public function testBuildGlobalContainerShouldLetGlobalServicesOverrideTheDefaultDefinitions(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ClientInterface::class, $client)
            ->buildGlobalContainer()
        ;

        $this->assertSame($client, $container->get(ClientInterface::class));
    }

    public function testBuildGlobalContainerShouldPreferThePresetContainerOverPayumsOwnServices(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $presetContainer = new Container();
        $presetContainer->set(ClientInterface::class, $client);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->setGlobalContainer($presetContainer)
            ->buildGlobalContainer()
        ;

        $this->assertSame($client, $container->get(ClientInterface::class));
    }

    public function testBuildGlobalContainerShouldFallBackToPayumsOwnServicesWhenThePresetContainerHasNone(): void
    {
        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->setGlobalContainer(new Container())
            ->buildGlobalContainer()
        ;

        $this->assertInstanceOf(HttpRequestVerifierInterface::class, $container->get(HttpRequestVerifierInterface::class));
        $this->assertInstanceOf(GenericTokenFactoryInterface::class, $container->get(GenericTokenFactoryInterface::class));
        $this->assertInstanceOf(TokenFactoryInterface::class, $container->get(TokenFactoryInterface::class));
        $this->assertInstanceOf(StorageInterface::class, $container->get('payum.security.token_storage'));
        $this->assertInstanceOf(ClientInterface::class, $container->get(ClientInterface::class));
    }

    public function testBuildGlobalContainerShouldBuildPayumsTokenFactoryOnTopOfThePresetContainersTokenStorage(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $presetContainer = new Container();
        $presetContainer->set('payum.security.token_storage', $tokenStorage);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->setGlobalContainer($presetContainer)
            ->buildGlobalContainer()
        ;

        $this->assertSame($tokenStorage, $container->get('payum.security.token_storage'));
        $this->assertSame(
            $tokenStorage,
            $this->readAttribute($container->get(HttpRequestVerifierInterface::class), 'tokenStorage')
        );
        $this->assertSame(
            $tokenStorage,
            $this->readAttribute($container->get(TokenFactoryInterface::class), 'tokenStorage')
        );
    }

    public function testBuildGlobalContainerShouldBuildPayumsGenericTokenFactoryOnTopOfThePresetContainersTokenFactory(): void
    {
        $tokenFactory = $this->createMock(TokenFactoryInterface::class);

        $presetContainer = new Container();
        $presetContainer->set(TokenFactoryInterface::class, $tokenFactory);

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->setGlobalContainer($presetContainer)
            ->buildGlobalContainer()
        ;

        $this->assertSame(
            $tokenFactory,
            $this->readAttribute($container->get(GenericTokenFactoryInterface::class), 'tokenFactory')
        );
    }

    public function testBuildGlobalContainerShouldAddDefaultStoragesWhenThePresetContainerHasNoTokenStorage(): void
    {
        $builder = new ExposedGlobalContainerPayumBuilder();
        $builder->setGlobalContainer(new Container());

        $container = $builder->buildGlobalContainer();

        $this->assertInstanceOf(StorageInterface::class, $container->get('payum.security.token_storage'));

        $ref = new ReflectionProperty($builder, 'storages');

        $this->assertArrayHasKey(Payment::class, $ref->getValue($builder));
    }

    public function testBuildGlobalContainerShouldNotAddDefaultStoragesWhenThePresetContainerHasATokenStorage(): void
    {
        $presetContainer = new Container();
        $presetContainer->set('payum.security.token_storage', $this->createMock(StorageInterface::class));

        $builder = new ExposedGlobalContainerPayumBuilder();
        $builder->setGlobalContainer($presetContainer);

        $builder->buildGlobalContainer();

        $ref = new ReflectionProperty($builder, 'storages');

        $this->assertSame([], $ref->getValue($builder));

        $ref = new ReflectionProperty($builder, 'tokenStorage');

        $this->assertNull($ref->getValue($builder));
    }

    public function testBuildGlobalContainerShouldStillExposeGlobalServicesWhenAContainerIsPreset(): void
    {
        $service = new stdClass();

        $container = (new ExposedGlobalContainerPayumBuilder())
            ->addDefaultStorages()
            ->setGlobalContainer(new Container())
            ->addGlobalService('acme.service', $service)
            ->buildGlobalContainer()
        ;

        $this->assertTrue($container->has('acme.service'));
        $this->assertSame($service, $container->get('acme.service'));
    }

    public function testGetPayumShouldUseTheSecurityServicesFromTheGlobalContainer(): void
    {
        $genericTokenFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $httpRequestVerifier = $this->createMock(HttpRequestVerifierInterface::class);

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(GenericTokenFactoryInterface::class, $genericTokenFactory)
            ->addGlobalService(HttpRequestVerifierInterface::class, $httpRequestVerifier)
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertSame($genericTokenFactory, $payum->getTokenFactory());
        $this->assertSame($httpRequestVerifier, $payum->getHttpRequestVerifier());
    }

    public function testGetPayumShouldUseThePresetGlobalContainer(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);
        $genericTokenFactory = $this->createMock(GenericTokenFactoryInterface::class);
        $httpRequestVerifier = $this->createMock(HttpRequestVerifierInterface::class);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            GenericTokenFactoryInterface::class => $genericTokenFactory,
            HttpRequestVerifierInterface::class => $httpRequestVerifier,
            'payum.security.token_storage' => $tokenStorage,
        ]);

        $payum = (new PayumBuilder())
            ->setTokenStorage($tokenStorage)
            ->setGlobalContainer($containerBuilder->build())
            ->getPayum()
        ;

        $this->assertSame($genericTokenFactory, $payum->getTokenFactory());
        $this->assertSame($httpRequestVerifier, $payum->getHttpRequestVerifier());
        $this->assertSame($tokenStorage, $payum->getTokenStorage());
    }

    /**
     * A preset global container bypasses the default storage set up, so the token storage has to be taken
     * from the container when it was not configured on the builder.
     */
    public function testGetPayumShouldTakeTheTokenStorageFromThePresetGlobalContainer(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            GenericTokenFactoryInterface::class => $this->createMock(GenericTokenFactoryInterface::class),
            HttpRequestVerifierInterface::class => $this->createMock(HttpRequestVerifierInterface::class),
            'payum.security.token_storage' => $tokenStorage,
        ]);

        $payum = (new PayumBuilder())
            ->setGlobalContainer($containerBuilder->build())
            ->getPayum()
        ;

        $this->assertSame($tokenStorage, $payum->getTokenStorage());
    }

    public function testGetPayumShouldPassTheTokenStorageOfThePresetGlobalContainerToTheCoreGatewayFactory(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            GenericTokenFactoryInterface::class => $this->createMock(GenericTokenFactoryInterface::class),
            HttpRequestVerifierInterface::class => $this->createMock(HttpRequestVerifierInterface::class),
            'payum.security.token_storage' => $tokenStorage,
        ]);

        (new PayumBuilder())
            ->setGlobalContainer($containerBuilder->build())
            ->setCoreGatewayFactory(function (array $config) use ($tokenStorage): CoreGatewayFactory {
                $this->assertArrayHasKey('payum.security.token_storage', $config);
                $this->assertSame($tokenStorage, $config['payum.security.token_storage']);

                return new CoreGatewayFactory($config);
            })
            ->getPayum()
        ;
    }

    public function testGetPayumShouldPreferTheExplicitlyConfiguredTokenStorageOverThePresetContainerOne(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            GenericTokenFactoryInterface::class => $this->createMock(GenericTokenFactoryInterface::class),
            HttpRequestVerifierInterface::class => $this->createMock(HttpRequestVerifierInterface::class),
            'payum.security.token_storage' => $this->createMock(StorageInterface::class),
        ]);

        $payum = (new PayumBuilder())
            ->setTokenStorage($tokenStorage)
            ->setGlobalContainer($containerBuilder->build())
            ->getPayum()
        ;

        $this->assertSame($tokenStorage, $payum->getTokenStorage());
    }

    public function testGetPayumShouldWorkWithAContainerProvidingNoneOfPayumsServices(): void
    {
        $payum = (new PayumBuilder())
            ->setGlobalContainer(new Container())
            ->getPayum()
        ;

        $this->assertInstanceOf(Payum::class, $payum);
        $this->assertInstanceOf(HttpRequestVerifierInterface::class, $payum->getHttpRequestVerifier());
        $this->assertInstanceOf(GenericTokenFactoryInterface::class, $payum->getTokenFactory());
        $this->assertInstanceOf(StorageInterface::class, $payum->getTokenStorage());
    }

    public function testShouldCreateGatewayWithTheContainerConfigurationFactory(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertSame(1, $factory->createGatewayCalls);
        $this->assertSame(0, $factory->createCalls);
        $this->assertSame($factory->gateway, $payum->getGateway('acme'));
    }

    public function testShouldPassTheGlobalServicesToTheGatewayContainer(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        $tokenStorage = $this->createMock(StorageInterface::class);
        $client = $this->createMock(ClientInterface::class);

        (new PayumBuilder())
            ->addDefaultStorages()
            ->setTokenStorage($tokenStorage)
            ->addGlobalService(ClientInterface::class, $client)
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $container = $factory->container;

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertSame($tokenStorage, $container->get('payum.security.token_storage'));
        $this->assertSame($client, $container->get(ClientInterface::class));
        $this->assertInstanceOf(HttpRequestVerifierInterface::class, $container->get(HttpRequestVerifierInterface::class));
        $this->assertInstanceOf(GenericTokenFactoryInterface::class, $container->get(GenericTokenFactoryInterface::class));
        $this->assertInstanceOf(TokenFactoryInterface::class, $container->get(TokenFactoryInterface::class));
        $this->assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactoryInterface::class));
        $this->assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactoryInterface::class));
        $this->assertInstanceOf(ClockInterface::class, $container->get(ClockInterface::class));
    }

    public function testShouldPassTheApplicationsClockToTheGatewayContainer(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        $clock = new FrozenClock('2026-01-01 12:00:00');

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ClockInterface::class, $clock)
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertSame($clock, $factory->container->get(ClockInterface::class));
    }

    public function testShouldShareTheGlobalServicesBetweenAllGatewayContainers(): void
    {
        $firstFactory = new ContainerConfigurationGatewayFactoryStub();
        $secondFactory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('first_factory', $firstFactory)
            ->addGatewayFactory('second_factory', $secondFactory)
            ->addGateway('first', [
                'factory' => 'first_factory',
            ])
            ->addGateway('second', [
                'factory' => 'second_factory',
            ])
            ->getPayum()
        ;

        $this->assertNotSame($firstFactory->container, $secondFactory->container);

        foreach ([
            'payum.security.token_storage',
            HttpRequestVerifierInterface::class,
            GenericTokenFactoryInterface::class,
            TokenFactoryInterface::class,
            ClientInterface::class,
            StreamFactoryInterface::class,
            RequestFactoryInterface::class,
            ClockInterface::class,
        ] as $serviceId) {
            $this->assertSame(
                $firstFactory->container->get($serviceId),
                $secondFactory->container->get($serviceId),
                sprintf('The "%s" service is not shared between the gateways', $serviceId)
            );
        }
    }

    public function testShouldPassTheStorageExtensionsToTheGatewayContainer(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        $storage = $this->createMock(StorageInterface::class);

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addStorage(GlobalContainerTestModel::class, $storage)
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertTrue($factory->container->has('payum.extension.storage_payum_core_tests_globalcontainertestmodel'));

        $extension = $factory->container->get('payum.extension.storage_payum_core_tests_globalcontainertestmodel');

        $this->assertInstanceOf(StorageExtension::class, $extension);
        $this->assertSame($storage, $this->readAttribute($extension, 'storage'));
    }

    public function testShouldPassTheGatewayConfigToTheGatewayContainer(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
                'acme.username' => 'aUsername',
                'acme.sandbox' => true,
            ])
            ->getPayum()
        ;

        $this->assertSame('aUsername', $factory->container->get('acme.username'));
        $this->assertTrue($factory->container->get('acme.sandbox'));
    }

    public function testShouldGiveTheGatewayConfigPrecedenceOverTheFactoryDefinitions(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub([
            'acme.username' => 'aFactoryUsername',
            'acme.sandbox' => true,
        ]);

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
                'acme.username' => 'aUsername',
            ])
            ->getPayum()
        ;

        $this->assertSame('aUsername', $factory->container->get('acme.username'));
        $this->assertTrue($factory->container->get('acme.sandbox'));
    }

    public function testShouldPassTheServicesAddedWithAddGlobalServiceToTheGatewayContainer(): void
    {
        $service = new stdClass();

        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService('acme.service', $service)
            ->addGlobalService(GlobalContainerTestModel::class, $model = new GlobalContainerTestModel())
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertTrue($factory->container->has('acme.service'));
        $this->assertSame($service, $factory->container->get('acme.service'));
        $this->assertSame($model, $factory->container->get(GlobalContainerTestModel::class));
    }

    public function testShouldShareTheServicesAddedWithAddGlobalServiceBetweenGateways(): void
    {
        $firstFactory = new ContainerConfigurationGatewayFactoryStub();
        $secondFactory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService('acme.service', static fn (): stdClass => new stdClass())
            ->addGatewayFactory('first_factory', $firstFactory)
            ->addGatewayFactory('second_factory', $secondFactory)
            ->addGateway('first', [
                'factory' => 'first_factory',
            ])
            ->addGateway('second', [
                'factory' => 'second_factory',
            ])
            ->getPayum()
        ;

        $this->assertSame(
            $firstFactory->container->get('acme.service'),
            $secondFactory->container->get('acme.service')
        );
    }

    public function testShouldPassTheEntriesOfAPresetGlobalContainerToTheGatewayContainer(): void
    {
        $service = new stdClass();

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions([
            GenericTokenFactoryInterface::class => $this->createMock(GenericTokenFactoryInterface::class),
            HttpRequestVerifierInterface::class => $this->createMock(HttpRequestVerifierInterface::class),
            'payum.security.token_storage' => $this->createMock(StorageInterface::class),
            'acme.service' => $service,
        ]);

        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->setGlobalContainer($containerBuilder->build())
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertSame($service, $factory->container->get('acme.service'));
    }

    public function testShouldResolveServicesOfAPresetContainerWhichCannotEnumerateItsEntriesFromAGateway(): void
    {
        $service = new stdClass();

        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->setGlobalContainer(new OpaqueContainer([
                'acme.service' => $service,
            ]))
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertTrue($factory->container->has('acme.service'));
        $this->assertSame($service, $factory->container->get('acme.service'));
    }

    public function testShouldStillResolvePayumsOwnServicesFromAGatewayWhenThePresetContainerCannotEnumerate(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->setGlobalContainer(new OpaqueContainer())
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertInstanceOf(StorageInterface::class, $factory->container->get('payum.security.token_storage'));
        $this->assertInstanceOf(GenericTokenFactoryInterface::class, $factory->container->get(GenericTokenFactoryInterface::class));
        $this->assertInstanceOf(ClientInterface::class, $factory->container->get(ClientInterface::class));
    }

    public function testShouldNotDelegateTheContainerItselfToThePresetGlobalContainer(): void
    {
        $globalContainer = (new ContainerBuilder())->build();
        $globalContainer->set(GenericTokenFactoryInterface::class, $this->createMock(GenericTokenFactoryInterface::class));
        $globalContainer->set(HttpRequestVerifierInterface::class, $this->createMock(HttpRequestVerifierInterface::class));
        $globalContainer->set('payum.security.token_storage', $this->createMock(StorageInterface::class));

        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->setGlobalContainer($globalContainer)
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        // A gateway is injected with its own container, never with the global one
        $this->assertNotSame($globalContainer, $factory->container->get(ContainerInterface::class));
        $this->assertNotSame($globalContainer, $factory->container);
    }

    public function testShouldGiveTheGlobalServicesPrecedenceOverTheGatewayFactoryDefaults(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $factory = new ContainerConfigurationGatewayFactoryStub([
            ClientInterface::class => $this->createMock(ClientInterface::class),
        ]);

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ClientInterface::class, $client)
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertSame($client, $factory->container->get(ClientInterface::class));
    }

    public function testShouldGiveTheGatewayConfigPrecedenceOverTheGlobalServices(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $gatewayClient = $this->createMock(ClientInterface::class);

        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(ClientInterface::class, $client)
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
                ClientInterface::class => $gatewayClient,
            ])
            ->getPayum()
        ;

        $this->assertSame($gatewayClient, $factory->container->get(ClientInterface::class));
    }

    /**
     * Guards the gateway factory pattern documented in docs/di.
     */
    public function testShouldSupportTheDocumentedGatewayFactoryPattern(): void
    {
        $logger = new NullLogger();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGlobalService(LoggerInterface::class, $logger)
            ->addGatewayFactory('documented', new DocumentedGatewayFactory())
            ->addGateway('documented', [
                'factory' => 'documented',
                'documented.client_id' => 'theClientId',
                'documented.sandbox' => false,
            ])
            ->getPayum()
        ;

        $actions = $this->readAttribute($payum->getGateway('documented'), 'actions');

        $documentedAction = null;
        foreach ($actions as $action) {
            if ($action instanceof DocumentedCaptureAction) {
                $documentedAction = $action;
            }
        }

        $this->assertInstanceOf(DocumentedCaptureAction::class, $documentedAction);

        // the gateway config reaches the api through get()
        $this->assertSame('theClientId', $documentedAction->api->clientId);
        $this->assertFalse($documentedAction->api->sandbox);

        // the global service reaches the action
        $this->assertSame($logger, $documentedAction->logger);

        // the core actions are still registered
        $this->assertContains(GetHttpRequestAction::class, array_map(get_class(...), $actions));
    }

    /**
     * Guards the framework integration documented in docs/di: an application container which cannot list
     * its entries plus addGlobalService() for what the gateways need injected.
     */
    public function testShouldInjectAGlobalServiceIntoAnActionWhenThePresetContainerCannotEnumerate(): void
    {
        $logger = new NullLogger();

        $payum = (new PayumBuilder())
            ->setGlobalContainer(new OpaqueContainer())
            ->addGlobalService(LoggerInterface::class, $logger)
            ->addGatewayFactory('documented', new DocumentedGatewayFactory())
            ->addGateway('documented', [
                'factory' => 'documented',
            ])
            ->getPayum()
        ;

        $documentedAction = null;
        foreach ($this->readAttribute($payum->getGateway('documented'), 'actions') as $action) {
            if ($action instanceof DocumentedCaptureAction) {
                $documentedAction = $action;
            }
        }

        $this->assertInstanceOf(DocumentedCaptureAction::class, $documentedAction);
        $this->assertSame($logger, $documentedAction->logger);

        // Payum's own services are still there, without the application declaring any of them
        $this->assertInstanceOf(GenericTokenFactory::class, $payum->getTokenFactory());
        $this->assertInstanceOf(HttpRequestVerifier::class, $payum->getHttpRequestVerifier());
    }

    /**
     * A container which is not a PHP-DI one but is able to report its entries has those entries turned
     * into definitions of the gateway containers, so that they can be autowired.
     */
    public function testShouldInjectAServiceOfAListablePresetContainerIntoAnAction(): void
    {
        $logger = new NullLogger();

        $payum = (new PayumBuilder())
            ->setGlobalContainer(new ListableContainer([
                LoggerInterface::class => $logger,
            ]))
            ->addGatewayFactory('documented', new DocumentedGatewayFactory())
            ->addGateway('documented', [
                'factory' => 'documented',
            ])
            ->getPayum()
        ;

        $documentedAction = null;
        foreach ($this->readAttribute($payum->getGateway('documented'), 'actions') as $action) {
            if ($action instanceof DocumentedCaptureAction) {
                $documentedAction = $action;
            }
        }

        $this->assertInstanceOf(DocumentedCaptureAction::class, $documentedAction);
        $this->assertSame($logger, $documentedAction->logger);
    }

    public function testShouldShareTheEntriesOfAListablePresetContainerWithTheGatewayContainer(): void
    {
        $service = new stdClass();

        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->setGlobalContainer(new ListableContainer([
                'acme.service' => $service,
            ]))
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertTrue($factory->container->has('acme.service'));
        $this->assertSame($service, $factory->container->get('acme.service'));
    }

    public function testShouldStillResolvePayumsOwnServicesWithAListablePresetContainer(): void
    {
        $factory = new ContainerConfigurationGatewayFactoryStub();

        (new PayumBuilder())
            ->setGlobalContainer(new ListableContainer())
            ->addGatewayFactory('acme_factory', $factory)
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
            ->getPayum()
        ;

        $this->assertInstanceOf(StorageInterface::class, $factory->container->get('payum.security.token_storage'));
        $this->assertInstanceOf(GenericTokenFactoryInterface::class, $factory->container->get(GenericTokenFactoryInterface::class));
        $this->assertInstanceOf(ClientInterface::class, $factory->container->get(ClientInterface::class));
    }

    public function testShouldStillSupportTheDeprecatedCreateApiOnADocumentedGatewayFactory(): void
    {
        $gateway = (new DocumentedGatewayFactory())->create([
            LoggerInterface::class => new NullLogger(),
        ]);

        $this->assertContains(
            DocumentedCaptureAction::class,
            array_map(get_class(...), $this->readAttribute($gateway, 'actions'))
        );
    }

    public function testShouldFallBackToTheLegacyGatewayFactoryApi(): void
    {
        $factory = new LegacyGatewayFactoryStub();

        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('legacy_factory', $factory)
            ->addGateway('legacy', [
                'factory' => 'legacy_factory',
                'acme.username' => 'aUsername',
            ])
            ->getPayum()
        ;

        $this->assertSame(1, $factory->createCalls);
        $this->assertSame($factory->gateway, $payum->getGateway('legacy'));
        $this->assertSame([
            'acme.username' => 'aUsername',
        ], $factory->receivedConfig);
        $this->assertArrayNotHasKey('factory', $factory->receivedConfig);
    }

    public function testShouldTriggerDeprecationForTheLegacyGatewayFactoryApi(): void
    {
        $builder = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('legacy_factory', new LegacyGatewayFactoryStub())
            ->addGateway('legacy', [
                'factory' => 'legacy_factory',
            ])
        ;

        $deprecations = $this->collectDeprecations(static function () use ($builder): void {
            $builder->getPayum();
        });

        $this->assertContains(
            sprintf(
                'Since payum/core 2.0.0: Not implementing %s for gateway factory %s is deprecated.',
                ContainerConfiguration::class,
                LegacyGatewayFactoryStub::class
            ),
            $deprecations
        );
    }

    public function testShouldNotTriggerTheLegacyGatewayFactoryDeprecationForAContainerConfigurationFactory(): void
    {
        $builder = (new PayumBuilder())
            ->addDefaultStorages()
            ->addGatewayFactory('acme_factory', new ContainerConfigurationGatewayFactoryStub())
            ->addGateway('acme', [
                'factory' => 'acme_factory',
            ])
        ;

        $deprecations = $this->collectDeprecations(static function () use ($builder): void {
            $builder->getPayum();
        });

        $this->assertNotContains(
            sprintf(
                'Since payum/core 2.0.0: Not implementing %s for gateway factory %s is deprecated.',
                ContainerConfiguration::class,
                ContainerConfigurationGatewayFactoryStub::class
            ),
            $deprecations
        );
    }

    /**
     * @return list<string>
     */
    private function collectDeprecations(callable $callback): array
    {
        $deprecations = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            $deprecations[] = $errstr;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $deprecations;
    }
}

class ExposedGlobalContainerPayumBuilder extends PayumBuilder
{
    public function buildGlobalContainer(): ContainerInterface
    {
        return parent::buildGlobalContainer();
    }
}

class ContainerConfigurationGatewayFactoryStub implements GatewayFactoryInterface, ContainerConfiguration, CreatesGateway
{
    public ?ContainerInterface $container = null;

    public int $createGatewayCalls = 0;

    public int $createCalls = 0;

    public Gateway $gateway;

    /**
     * @param array<string, mixed> $definitions
     */
    public function __construct(
        private array $definitions = []
    ) {
        $this->gateway = new Gateway();
    }

    public function configureContainer(): array
    {
        return $this->definitions;
    }

    public function createGateway(ContainerInterface $container): Gateway
    {
        ++$this->createGatewayCalls;
        $this->container = $container;

        return $this->gateway;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function createConfig(array $config = []): array
    {
        return $config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config = []): Gateway
    {
        ++$this->createCalls;

        return $this->gateway;
    }
}

class LegacyGatewayFactoryStub implements GatewayFactoryInterface
{
    public int $createCalls = 0;

    /**
     * @var array<string, mixed>
     */
    public array $receivedConfig = [];

    public Gateway $gateway;

    public function __construct()
    {
        $this->gateway = new Gateway();
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function createConfig(array $config = []): array
    {
        return $config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function create(array $config = []): Gateway
    {
        ++$this->createCalls;
        $this->receivedConfig = $config;

        return $this->gateway;
    }
}

class GlobalContainerTestModel
{
}

/**
 * A PSR-11 container which, like most framework containers, cannot list what it holds.
 */
class OpaqueContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(
        private array $services = []
    ) {
    }

    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new class('Service ' . $id . ' not found') extends InvalidArgumentException implements NotFoundExceptionInterface {
            };
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}

/**
 * A PSR-11 container which is not a PHP-DI one but is able to report the entries it holds.
 */
class ListableContainer extends OpaqueContainer implements ListableContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(
        private array $services = []
    ) {
        parent::__construct($services);
    }

    public function getKnownEntryNames(): array
    {
        return array_keys($this->services);
    }
}

class DocumentedApi
{
    public function __construct(
        public string $clientId,
        public bool $sandbox
    ) {
    }
}

class DocumentedCaptureAction implements ActionInterface
{
    public function __construct(
        public DocumentedApi $api,
        public LoggerInterface $logger
    ) {
    }

    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}

class DocumentedGatewayFactory extends CoreGatewayFactory
{
    public function configureContainer(): array
    {
        return array_merge(parent::configureContainer(), [
            'documented.client_id' => '',
            'documented.sandbox' => true,

            DocumentedApi::class => autowire()
                ->constructor(
                    clientId: get('documented.client_id'),
                    sandbox: get('documented.sandbox')
                ),

            DocumentedCaptureAction::class => autowire()
                ->constructorParameter('api', get(DocumentedApi::class)),
        ]);
    }

    public function getActions(): array
    {
        return array_merge(parent::getActions(), [
            DocumentedCaptureAction::class,
        ]);
    }
}
