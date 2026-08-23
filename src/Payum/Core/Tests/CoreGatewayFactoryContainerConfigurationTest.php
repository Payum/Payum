<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use DI\Container;
use DI\ContainerBuilder;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\StreamFactory;
use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\AuthorizePaymentAction;
use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\Action\PrependActionInterface;
use Payum\Core\Action\RenderTemplateAction;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\TwigRenderer;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\PrependExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryConfigInterface;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Template\RendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use ReflectionClass;
use stdClass;
use Twig\Environment;
use Twig\Loader\ChainLoader;

final class CoreGatewayFactoryContainerConfigurationTest extends TestCase
{
    public function testShouldImplementContainerConfigurationInterface(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(ContainerConfiguration::class));
    }

    public function testShouldImplementGatewayFactoryConfigInterface(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryConfigInterface::class));
    }

    public function testShouldStillImplementGatewayFactoryInterface(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    public function testConfigureContainerShouldReturnDefinitionsForAllDefaultServices(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $this->assertIsArray($definitions);

        foreach ([
            'httplug.message_factory',
            'httplug.stream_factory',
            'httplug.client',
            ClientInterface::class,
            StreamFactoryInterface::class,
            RequestFactoryInterface::class,
            ResponseFactoryInterface::class,
            'payum.http_client',
            'payum.http_stream_factory',
            'payum.http_message_factory',
            'payum.template.layout',
            'twig.env',
            Environment::class,
            HttpClient::class,
            'payum.default_options',
            'payum.required_options',
            'payum.prepend_actions',
            'payum.prepend_apis',
            'payum.prepend_extensions',
            'payum.api.http_client',
            'payum.paths',
            'payum.action.get_http_request',
            'payum.action.capture_payment',
            'payum.action.authorize_payment',
            'payum.action.payout_payout',
            'payum.action.execute_same_request_with_model_details',
            'payum.action.get_currency',
            'payum.action.render_template',
            'payum.action.get_token',
            'payum.extension.endless_cycle_detector',
            RendererInterface::class,
            GetHttpRequestAction::class,
            RenderTemplateAction::class,
            GetTokenAction::class,
        ] as $expectedId) {
            $this->assertArrayHasKey($expectedId, $definitions);
        }
    }

    public function testConfigureContainerShouldNotRegisterTokenStorageByDefault(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $this->assertArrayNotHasKey('payum.security.token_storage', $definitions);
    }

    public function testConfigureContainerShouldIncludeDefaultConfigGivenInConstructor(): void
    {
        $factory = new CoreGatewayFactory([
            'foo' => 'fooVal',
            'payum.security.token_storage' => $tokenStorage = $this->createMock(StorageInterface::class),
        ]);

        $definitions = $factory->configureContainer();

        $this->assertSame('fooVal', $definitions['foo']);
        $this->assertSame($tokenStorage, $definitions['payum.security.token_storage']);
    }

    public function testConfigureContainerShouldGiveTheConstructorDefaultConfigPrecedenceOverTheBuiltInDefinitions(): void
    {
        $factory = new CoreGatewayFactory([
            'payum.template.layout' => 'aCustomLayout.html.twig',
            'payum.paths' => [
                'AcmeGateway' => '/path/to/acme/views',
            ],
        ]);

        $definitions = $factory->configureContainer();

        $this->assertSame('aCustomLayout.html.twig', $definitions['payum.template.layout']);
        $this->assertSame([
            'AcmeGateway' => '/path/to/acme/views',
        ], $definitions['payum.paths']);
    }

    public function testConfigureContainerShouldProvideStaticDefaults(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $this->assertSame('@PayumCore/layout.html.twig', $definitions['payum.template.layout']);
        $this->assertSame([], $definitions['payum.default_options']);
        $this->assertSame([], $definitions['payum.required_options']);
        $this->assertSame([], $definitions['payum.prepend_actions']);
        $this->assertSame([], $definitions['payum.prepend_apis']);
        $this->assertSame([], $definitions['payum.prepend_extensions']);
        $this->assertSame([
            'PayumCore' => dirname(__DIR__) . '/Resources/views',
        ], $definitions['payum.paths']);
    }

    public function testShouldResolvePsr17And18ServicesFromContainer(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(ClientInterface::class, $container->get(ClientInterface::class));
        $this->assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactoryInterface::class));
        $this->assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactoryInterface::class));
    }

    public function testShouldBuildARequestWhoseBodyCanBeRead(): void
    {
        $request = $this->buildContainer()->get(ServerRequestInterface::class);

        // A PSP signs the raw body. A request built from superglobals alone has none.
        $this->assertSame('php://input', $request->getBody()->getMetadata('uri'));
        $this->assertTrue($request->getBody()->isReadable());
    }

    public function testShouldResolvePayumHttpClientAsHttplugClientWrappingThePsr18Client(): void
    {
        $container = $this->buildContainer();

        $httpClient = $container->get('payum.http_client');

        $this->assertInstanceOf(HttplugClient::class, $httpClient);
        $this->assertSame($container->get(ClientInterface::class), $this->readAttribute($httpClient, 'client'));
    }

    public function testShouldResolveLegacyHttpServiceNamesToThePsrServices(): void
    {
        $container = $this->buildContainer();

        $this->assertSame($container->get(StreamFactoryInterface::class), $container->get('payum.http_stream_factory'));
        $this->assertSame($container->get(RequestFactoryInterface::class), $container->get('payum.http_message_factory'));
        $this->assertSame($container->get('payum.http_client'), $container->get('payum.api.http_client'));
        $this->assertSame($container->get('payum.http_client'), $container->get(HttpClient::class));
    }

    public function testShouldAliasResponseFactoryInterfaceToRequestFactoryInterface(): void
    {
        $container = $this->buildContainer();

        $this->assertSame($container->get(RequestFactoryInterface::class), $container->get(ResponseFactoryInterface::class));
    }

    public function testShouldAliasTwigEnvironmentClassToTwigEnvService(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(Environment::class, $container->get('twig.env'));
        $this->assertSame($container->get('twig.env'), $container->get(Environment::class));
    }

    public function testShouldCreateTwigEnvironmentWithChainLoader(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(ChainLoader::class, $container->get('twig.env')->getLoader());
    }

    public function testShouldResolveDeprecatedActionDefinitions(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(GetHttpRequestAction::class, $container->get('payum.action.get_http_request'));
        $this->assertInstanceOf(CapturePaymentAction::class, $container->get('payum.action.capture_payment'));
        $this->assertInstanceOf(AuthorizePaymentAction::class, $container->get('payum.action.authorize_payment'));
        $this->assertInstanceOf(PayoutPayoutAction::class, $container->get('payum.action.payout_payout'));
        $this->assertInstanceOf(ExecuteSameRequestWithModelDetailsAction::class, $container->get('payum.action.execute_same_request_with_model_details'));
        $this->assertInstanceOf(GetCurrencyAction::class, $container->get('payum.action.get_currency'));
        $this->assertInstanceOf(RenderTemplateAction::class, $container->get('payum.action.render_template'));
        $this->assertInstanceOf(EndlessCycleDetectorExtension::class, $container->get('payum.extension.endless_cycle_detector'));
    }

    public function testShouldResolveRenderTemplateActionWithTheContainersRenderer(): void
    {
        $container = $this->buildContainer();

        $action = $container->get(RenderTemplateAction::class);

        $this->assertInstanceOf(RenderTemplateAction::class, $action);
        $this->assertSame($container->get(RendererInterface::class), $this->readAttribute($action, 'renderer'));
    }

    public function testShouldResolveTheDefaultRendererToTwigOnTheContainersEnvironment(): void
    {
        $container = $this->buildContainer();

        $renderer = $container->get(RendererInterface::class);

        $this->assertInstanceOf(TwigRenderer::class, $renderer);
        $this->assertSame($container->get('twig.env'), $this->readAttribute($renderer, 'twig'));
        $this->assertSame('@PayumCore/layout.html.twig', $this->readAttribute($renderer, 'layout'));
    }

    public function testShouldResolveGetHttpRequestActionWithTheContainersServerRequest(): void
    {
        $container = $this->buildContainer();

        $action = $container->get(GetHttpRequestAction::class);

        $this->assertInstanceOf(GetHttpRequestAction::class, $action);
        $this->assertSame($container->get(ServerRequestInterface::class), $this->readAttribute($action, 'httpRequest'));
    }

    public function testShouldResolveDeprecatedGetTokenActionEntryToNullWhenTokenStorageIsMissing(): void
    {
        $container = $this->buildContainer();

        $this->assertFalse($container->has('payum.security.token_storage'));
        $this->assertNull($container->get('payum.action.get_token'));
    }

    public function testShouldResolveDeprecatedGetTokenActionEntryWhenTokenStorageIsConfigured(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $container = $this->buildContainer([
            'payum.security.token_storage' => $tokenStorage,
        ]);

        $action = $container->get('payum.action.get_token');

        $this->assertInstanceOf(GetTokenAction::class, $action);
        $this->assertSame($tokenStorage, $this->readAttribute($action, 'tokenStorage'));
    }

    public function testShouldThrowWhenGetTokenActionClassIsResolvedWithoutTokenStorage(): void
    {
        $container = $this->buildContainer();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Token storage must be configured to use GetTokenAction');

        $container->get(GetTokenAction::class);
    }

    public function testShouldResolveGetTokenActionClassWhenTokenStorageIsConfigured(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $container = $this->buildContainer([
            'payum.security.token_storage' => $tokenStorage,
        ]);

        $action = $container->get(GetTokenAction::class);

        $this->assertInstanceOf(GetTokenAction::class, $action);
        $this->assertSame($tokenStorage, $this->readAttribute($action, 'tokenStorage'));
    }

    public function testShouldResolveDeprecatedHttplugMessageFactory(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $deprecations = $this->collectDeprecations(function () use ($definitions, &$messageFactory): void {
            $messageFactory = $definitions['httplug.message_factory']();
        });

        $this->assertInstanceOf(MessageFactory::class, $messageFactory);
        $this->assertContains(
            'Using "httplug.message_factory" is deprecated, use "payum.http_message_factory" instead, which will return a PSR-17 RequestFactoryInterface since payum/core 2.0.0',
            $deprecations
        );
    }

    public function testShouldResolveDeprecatedHttplugStreamFactory(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $deprecations = $this->collectDeprecations(function () use ($definitions, &$streamFactory): void {
            $streamFactory = $definitions['httplug.stream_factory']();
        });

        $this->assertInstanceOf(StreamFactory::class, $streamFactory);
        $this->assertContains(
            'Using "httplug.stream_factory" is deprecated, use "payum.http_stream_factory" instead which will return a PSR-17 StreamFactoryInterface since payum/core 2.0.0',
            $deprecations
        );
    }

    public function testShouldResolveDeprecatedHttplugClient(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $deprecations = $this->collectDeprecations(function () use ($definitions, &$client): void {
            $client = $definitions['httplug.client'](new ArrayObject());
        });

        $this->assertInstanceOf(ClientInterface::class, $client);
        $this->assertContains(
            'Using "httplug.client" is deprecated, use "payum.http_client" instead which will return a PSR-18 ClientInterface since payum/core 2.0.0',
            $deprecations
        );
    }

    public function testGetActionsShouldReturnTheDefaultActionClasses(): void
    {
        $this->assertSame([
            GetHttpRequestAction::class,
            CapturePaymentAction::class,
            AuthorizePaymentAction::class,
            PayoutPayoutAction::class,
            ExecuteSameRequestWithModelDetailsAction::class,
            RenderTemplateAction::class,
            GetCurrencyAction::class,
            GetTokenAction::class,
        ], (new CoreGatewayFactory())->getActions());
    }

    public function testGetExtensionsShouldReturnTheDefaultExtensionClasses(): void
    {
        $this->assertSame([
            EndlessCycleDetectorExtension::class,
        ], (new CoreGatewayFactory())->getExtensions());
    }

    public function testCreateGatewayShouldReturnGatewayWithAllDefaultActionsAndExtensions(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->createGateway($this->buildContainer());

        $this->assertInstanceOf(Gateway::class, $gateway);

        $actions = $this->readAttribute($gateway, 'actions');

        $this->assertContainsOnlyInstancesOf(ActionInterface::class, $actions);
        $this->assertSame([
            GetHttpRequestAction::class,
            CapturePaymentAction::class,
            AuthorizePaymentAction::class,
            PayoutPayoutAction::class,
            ExecuteSameRequestWithModelDetailsAction::class,
            RenderTemplateAction::class,
            GetCurrencyAction::class,
        ], array_map(get_class(...), $actions));

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');

        $this->assertSame([
            EndlessCycleDetectorExtension::class,
        ], array_map(get_class(...), $extensions));
    }

    public function testCreateGatewayShouldSkipGetTokenActionWhenTokenStorageIsNotConfigured(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->createGateway($this->buildContainer());

        $actions = $this->readAttribute($gateway, 'actions');

        $this->assertNotContains(GetTokenAction::class, array_map(get_class(...), $actions));
    }

    public function testCreateGatewayShouldAddGetTokenActionWhenTokenStorageIsConfigured(): void
    {
        $tokenStorage = $this->createMock(StorageInterface::class);

        $factory = new CoreGatewayFactory();

        $gateway = $factory->createGateway($this->buildContainer([
            'payum.security.token_storage' => $tokenStorage,
        ]));

        $actions = $this->readAttribute($gateway, 'actions');

        $this->assertContains(GetTokenAction::class, array_map(get_class(...), $actions));
    }

    public function testCreateGatewayShouldResolveActionsFromTheContainer(): void
    {
        $expectedAction = new CoreGatewayFactoryTestAction();

        $factory = new CoreGatewayFactoryWithCustomServices([CoreGatewayFactoryTestAction::class], []);

        $gateway = $factory->createGateway($this->buildContainer([
            CoreGatewayFactoryTestAction::class => $expectedAction,
        ]));

        $this->assertSame([$expectedAction], $this->readAttribute($gateway, 'actions'));
    }

    public function testCreateGatewayShouldAutowireActionsWhichAreNotDefinedInTheContainer(): void
    {
        $factory = new CoreGatewayFactoryWithCustomServices([CoreGatewayFactoryTestAction::class], []);

        $gateway = $factory->createGateway($this->buildContainer());

        $this->assertSame([CoreGatewayFactoryTestAction::class], array_map(
            get_class(...),
            $this->readAttribute($gateway, 'actions')
        ));
    }

    public function testCreateGatewayShouldAcceptExtensionInstances(): void
    {
        $expectedExtension = new CoreGatewayFactoryTestExtension();

        $factory = new CoreGatewayFactoryWithCustomServices([], [$expectedExtension]);

        $gateway = $factory->createGateway($this->buildContainer());

        $this->assertSame(
            [$expectedExtension],
            $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions')
        );
    }

    public function testCreateGatewayShouldPrependActionsResolvedFromContainerImplementingPrependActionInterface(): void
    {
        $factory = new CoreGatewayFactoryWithCustomServices(
            [CoreGatewayFactoryTestAction::class, CoreGatewayFactoryTestPrependAction::class],
            []
        );

        $gateway = $factory->createGateway($this->buildContainer());

        $this->assertSame([
            CoreGatewayFactoryTestPrependAction::class,
            CoreGatewayFactoryTestAction::class,
        ], array_map(get_class(...), $this->readAttribute($gateway, 'actions')));
    }

    public function testCreateGatewayShouldPrependExtensionsImplementingPrependExtensionInterface(): void
    {
        $regularExtension = new CoreGatewayFactoryTestExtension();
        $prependExtension = new CoreGatewayFactoryTestPrependExtension();

        $factory = new CoreGatewayFactoryWithCustomServices([], [$regularExtension, $prependExtension]);

        $gateway = $factory->createGateway($this->buildContainer());

        $this->assertSame(
            [$prependExtension, $regularExtension],
            $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions')
        );
    }

    public function testCreateGatewayShouldPrependExtensionsResolvedFromContainerImplementingPrependExtensionInterface(): void
    {
        $factory = new CoreGatewayFactoryWithCustomServices(
            [],
            [CoreGatewayFactoryTestExtension::class, CoreGatewayFactoryTestPrependExtension::class]
        );

        $gateway = $factory->createGateway($this->buildContainer());

        $this->assertSame([
            CoreGatewayFactoryTestPrependExtension::class,
            CoreGatewayFactoryTestExtension::class,
        ], array_map(get_class(...), $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions')));
    }

    public function testCreateGatewayShouldReuseTheGatewayGivenAsSecondArgument(): void
    {
        $existingAction = new CoreGatewayFactoryTestAction();

        $existingGateway = new Gateway();
        $existingGateway->addAction($existingAction);

        $factory = new CoreGatewayFactoryWithCustomServices([CoreGatewayFactoryTestPrependAction::class], []);

        $gateway = $factory->createGateway($this->buildContainer(), $existingGateway);

        $this->assertSame($existingGateway, $gateway);
        $this->assertSame([
            CoreGatewayFactoryTestPrependAction::class,
            CoreGatewayFactoryTestAction::class,
        ], array_map(get_class(...), $this->readAttribute($gateway, 'actions')));
    }

    public function testCreateGatewayShouldIgnoreSecondArgumentWhichIsNotAGateway(): void
    {
        $factory = new CoreGatewayFactoryWithCustomServices([], []);

        $gateway = $factory->createGateway($this->buildContainer(), new stdClass());

        $this->assertInstanceOf(Gateway::class, $gateway);
        $this->assertSame([], $this->readAttribute($gateway, 'actions'));
    }

    public function testCreateGatewayShouldRegisterPayumCoreTwigPath(): void
    {
        $factory = new CoreGatewayFactory();

        $container = $this->buildContainer();

        $factory->createGateway($container);

        $this->assertTrue($container->get('twig.env')->getLoader()->exists('@PayumCore/layout.html.twig'));
    }

    public function testCreateGatewayShouldRegisterTheConfiguredTwigPaths(): void
    {
        $factory = new CoreGatewayFactory();

        $container = $this->buildContainer([
            'payum.paths' => [
                'PayumCoreTestNamespace' => dirname(__DIR__) . '/Resources/views',
            ],
        ]);

        $factory->createGateway($container);

        $loader = $container->get('twig.env')->getLoader();

        $this->assertTrue($loader->exists('@PayumCoreTestNamespace/layout.html.twig'));
        $this->assertTrue($loader->exists('@PayumCore/layout.html.twig'));
    }

    public function testCreateShouldTriggerDeprecation(): void
    {
        $factory = new CoreGatewayFactory();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->create();
        });

        $this->assertContains(
            'Since payum/core 2.0.0: The Payum\Core\CoreGatewayFactory::create is deprecated. Implement the Payum\Core\DI\ContainerConfiguration interface instead.',
            $deprecations
        );
    }

    public function testCreateShouldTriggerDeprecationForEveryDeprecatedBuildMethod(): void
    {
        $factory = new CoreGatewayFactory();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->create();
        });

        foreach (['buildClosures', 'buildActions', 'buildApis', 'buildExtensions'] as $method) {
            $this->assertContains(
                sprintf(
                    'Since payum/core 2.0.0: The Payum\Core\CoreGatewayFactory::%s is deprecated. Implement the Payum\Core\DI\ContainerConfiguration interface instead.',
                    $method
                ),
                $deprecations
            );
        }
    }

    public function testCreateShouldTriggerDeprecationForApiConfig(): void
    {
        $factory = new CoreGatewayFactory();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->create();
        });

        $this->assertContains(
            'The payum.api.* config is deprecated and will be removed in 3.0. Use dependency-injection to inject the api into the action instead.',
            $deprecations
        );
    }

    public function testCreateShouldRegisterBackwardCompatibleArrayObjectServiceDelegatingToTheContainer(): void
    {
        $capturedArrayObject = null;

        $factory = new CoreGatewayFactory();

        $factory->create([
            'payum.action.probe' => static function (ContainerInterface $container) use (&$capturedArrayObject): ActionInterface {
                $capturedArrayObject = $container->get(ArrayObject::class);

                return new CoreGatewayFactoryTestAction();
            },
        ]);

        $this->assertInstanceOf(ArrayObject::class, $capturedArrayObject);
        $this->assertSame('@PayumCore/layout.html.twig', $capturedArrayObject['payum.template.layout']);
        $this->assertInstanceOf(GetHttpRequestAction::class, $capturedArrayObject['payum.action.get_http_request']);
    }

    public function testCreateShouldRegisterBackwardCompatibleArrayObjectServiceCheckingTheContainerForExistence(): void
    {
        $capturedArrayObject = null;

        $factory = new CoreGatewayFactory();

        $factory->create([
            'payum.action.probe' => static function (ContainerInterface $container) use (&$capturedArrayObject): ActionInterface {
                $capturedArrayObject = $container->get(ArrayObject::class);

                return new CoreGatewayFactoryTestAction();
            },
        ]);

        $this->assertInstanceOf(ArrayObject::class, $capturedArrayObject);
        $this->assertArrayHasKey('payum.template.layout', $capturedArrayObject);
        $this->assertArrayHasKey('payum.action.probe', $capturedArrayObject);
        $this->assertArrayNotHasKey('payum.security.token_storage', $capturedArrayObject);
        $this->assertArrayNotHasKey('an.unknown.service', $capturedArrayObject);
    }

    public function testCreateShouldPrependLegacyConfiguredActionImplementingPrependActionInterface(): void
    {
        $regularAction = new CoreGatewayFactoryTestAction();
        $prependAction = new CoreGatewayFactoryTestPrependAction();

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.action.regular' => $regularAction,
            'payum.action.prepend' => $prependAction,
        ]);

        $actions = $this->readAttribute($gateway, 'actions');

        $this->assertSame($prependAction, $actions[0]);
        $this->assertSame($regularAction, $actions[1]);
    }

    public function testCreateShouldPrependLegacyConfiguredExtensionImplementingPrependExtensionInterface(): void
    {
        $regularExtension = new CoreGatewayFactoryTestExtension();
        $prependExtension = new CoreGatewayFactoryTestPrependExtension();

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.extension.regular' => $regularExtension,
            'payum.extension.prepend' => $prependExtension,
        ]);

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');

        $this->assertSame($prependExtension, $extensions[0]);
        $this->assertSame($regularExtension, $extensions[1]);
    }

    public function testCreateShouldStillHonourThePrependActionsConfigOption(): void
    {
        $firstAction = new CoreGatewayFactoryTestAction();
        $secondAction = new CoreGatewayFactoryTestAction();

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.action.first' => $firstAction,
            'payum.action.second' => $secondAction,
            'payum.prepend_actions' => ['payum.action.second'],
        ]);

        $actions = $this->readAttribute($gateway, 'actions');

        $this->assertSame($secondAction, $actions[0]);
        $this->assertSame($firstAction, $actions[1]);
    }

    public function testCreateShouldStillHonourThePrependExtensionsConfigOption(): void
    {
        $firstExtension = new CoreGatewayFactoryTestExtension();
        $secondExtension = new CoreGatewayFactoryTestExtension();

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.extension.first' => $firstExtension,
            'payum.extension.second' => $secondExtension,
            'payum.prepend_extensions' => ['payum.extension.second'],
        ]);

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');

        $this->assertSame($secondExtension, $extensions[0]);
        $this->assertSame($firstExtension, $extensions[1]);
    }

    public function testCreateShouldAlsoAddTheActionsAndExtensionsOfTheNewContainerConfiguration(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create();

        $actionClasses = array_map(get_class(...), $this->readAttribute($gateway, 'actions'));
        $extensionClasses = array_map(
            get_class(...),
            $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions')
        );

        $this->assertContains(GetHttpRequestAction::class, $actionClasses);
        $this->assertContains(RenderTemplateAction::class, $actionClasses);
        $this->assertContains(GetCurrencyAction::class, $actionClasses);
        $this->assertContains(EndlessCycleDetectorExtension::class, $extensionClasses);
    }

    public function testCreateShouldNotAddGetTokenActionWhenTokenStorageIsNotConfigured(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create();

        $this->assertNotContains(GetTokenAction::class, array_map(get_class(...), $this->readAttribute($gateway, 'actions')));
    }

    public function testCreateShouldAddGetTokenActionWhenTokenStorageIsConfigured(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.security.token_storage' => $this->createMock(StorageInterface::class),
        ]);

        $this->assertContains(GetTokenAction::class, array_map(get_class(...), $this->readAttribute($gateway, 'actions')));
    }

    public function testCreateConfigShouldTriggerDeprecation(): void
    {
        $factory = new CoreGatewayFactory();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->createConfig();
        });

        $this->assertContains(
            'Since payum/core 2.0.0: The Payum\Core\CoreGatewayFactory::createConfig is deprecated. Implement the Payum\Core\DI\ContainerConfiguration interface instead.',
            $deprecations
        );
    }

    public function testCreateConfigShouldReturnTheContainerDefinitionsWhenNoConfigIsGiven(): void
    {
        $factory = new CoreGatewayFactory();

        $this->assertEquals($factory->configureContainer(), $factory->createConfig());
    }

    public function testCreateConfigShouldLetTheGivenConfigOverrideTheContainerDefinitions(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([
            'payum.template.layout' => 'aCustomLayout.html.twig',
            'foo' => 'fooVal',
        ]);

        $this->assertSame('aCustomLayout.html.twig', $config['payum.template.layout']);
        $this->assertSame('fooVal', $config['foo']);
    }

    public function testCreateConfigShouldMergeThePayumPathsInsteadOfReplacingThem(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([
            'payum.paths' => [
                'AcmeGateway' => '/path/to/acme/views',
            ],
        ]);

        $this->assertSame([
            'PayumCore' => dirname(__DIR__) . '/Resources/views',
            'AcmeGateway' => '/path/to/acme/views',
        ], $config['payum.paths']);
    }

    public function testCreateConfigShouldAllowOverridingASinglePayumPath(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([
            'payum.paths' => [
                'PayumCore' => '/path/to/custom/views',
            ],
        ]);

        $this->assertSame([
            'PayumCore' => '/path/to/custom/views',
        ], $config['payum.paths']);
    }

    /**
     * @param array<string, mixed> $definitions
     */
    private function buildContainer(array $definitions = []): Container
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions((new CoreGatewayFactory())->configureContainer());
        $containerBuilder->addDefinitions($definitions);

        return $containerBuilder->build();
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

class CoreGatewayFactoryWithCustomServices extends CoreGatewayFactory
{
    /**
     * @param list<class-string<ActionInterface>> $actions
     * @param list<ExtensionInterface|class-string<ExtensionInterface>> $extensions
     */
    public function __construct(
        private array $actions,
        private array $extensions
    ) {
        parent::__construct();
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }
}

class CoreGatewayFactoryTestAction implements ActionInterface
{
    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}

class CoreGatewayFactoryTestPrependAction implements ActionInterface, PrependActionInterface
{
    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return false;
    }
}

class CoreGatewayFactoryTestExtension implements ExtensionInterface
{
    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
    }
}

class CoreGatewayFactoryTestPrependExtension implements ExtensionInterface, PrependExtensionInterface
{
    public function onPreExecute(Context $context): void
    {
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
    }
}
