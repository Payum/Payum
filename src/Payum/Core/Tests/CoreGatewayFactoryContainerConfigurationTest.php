<?php

declare(strict_types=1);

namespace Payum\Core\Tests;

use DI\Container;
use DI\ContainerBuilder;
use Http\Client\HttpClient;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Strategy\DiscoveryStrategy;
use Http\Message\MessageFactory;
use Http\Message\StreamFactory;
use LogicException;
use Money\Currencies;
use Money\Currencies\CurrencyList;
use Money\Currency;
use Money\MoneyFormatter;
use Money\MoneyParser;
use Nyholm\Psr7\Factory\Psr17Factory;
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
use Payum\Core\Clock\SystemClock;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Event\NullEventDispatcher;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\PrependExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryConfigInterface;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Template\RendererInterface;
use Payum\Core\Tests\Mocks\Clock\FrozenClock;
use Payum\Core\Tests\Mocks\Event\RecordingEventDispatcher;
use Psr\Clock\ClockInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
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
            UriFactoryInterface::class,
            ClockInterface::class,
            EventDispatcherInterface::class,
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
            GetCurrencyAction::class,
            GetTokenAction::class,
            Currencies::class,
            MoneyFormatter::class,
            MoneyParser::class,
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
        $this->assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactoryInterface::class));
        $this->assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactoryInterface::class));
    }

    public function testShouldResolveEachPsr17FactoryOnItsOwnWhenTheyAreSeparateClasses(): void
    {
        $requestFactory = new class() implements RequestFactoryInterface {
            public function createRequest(string $method, $uri): RequestInterface
            {
                return (new Psr17Factory())->createRequest($method, $uri);
            }
        };
        $responseFactory = new class() implements ResponseFactoryInterface {
            public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
            {
                return (new Psr17Factory())->createResponse($code, $reasonPhrase);
            }
        };
        $uriFactory = new class() implements UriFactoryInterface {
            public function createUri(string $uri = ''): UriInterface
            {
                return (new Psr17Factory())->createUri($uri);
            }
        };

        $container = $this->buildContainer([
            RequestFactoryInterface::class => $requestFactory,
            ResponseFactoryInterface::class => $responseFactory,
            UriFactoryInterface::class => $uriFactory,
        ]);

        $this->assertSame($requestFactory, $container->get(RequestFactoryInterface::class));
        $this->assertSame($responseFactory, $container->get(ResponseFactoryInterface::class));
        $this->assertSame($uriFactory, $container->get(UriFactoryInterface::class));
    }

    public function testShouldNotResolveTheResponseFactoryThroughTheRequestFactory(): void
    {
        $container = $this->buildContainer([
            RequestFactoryInterface::class => new class() implements RequestFactoryInterface {
                public function createRequest(string $method, $uri): RequestInterface
                {
                    return (new Psr17Factory())->createRequest($method, $uri);
                }
            },
        ]);

        $this->assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactoryInterface::class));
    }

    public function testShouldResolveASystemClockFromContainer(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(SystemClock::class, $container->get(ClockInterface::class));
    }

    public function testShouldResolveANoOpEventDispatcherFromContainer(): void
    {
        $container = $this->buildContainer();

        $this->assertInstanceOf(NullEventDispatcher::class, $container->get(EventDispatcherInterface::class));
    }

    public function testShouldLetTheGivenConfigReplaceTheEventDispatcher(): void
    {
        $events = new RecordingEventDispatcher();

        $builder = new ContainerBuilder();
        $builder->addDefinitions((new CoreGatewayFactory())->configureContainer());
        $builder->addDefinitions([
            EventDispatcherInterface::class => $events,
        ]);

        $this->assertSame($events, $builder->build()->get(EventDispatcherInterface::class));
    }

    public function testShouldLetTheGivenConfigReplaceTheClock(): void
    {
        $clock = new FrozenClock('2026-01-01 12:00:00');

        $builder = new ContainerBuilder();
        $builder->addDefinitions((new CoreGatewayFactory())->configureContainer());
        $builder->addDefinitions([
            ClockInterface::class => $clock,
        ]);

        $this->assertSame($clock, $builder->build()->get(ClockInterface::class));
    }

    public function testShouldBuildARequestWhoseBodyCanBeRead(): void
    {
        $request = $this->buildContainer()->get(ServerRequestInterface::class);

        // A PSP signs the raw body. A request built from superglobals alone has none.
        $this->assertSame('php://input', $request->getBody()->getMetadata('uri'));
        $this->assertTrue($request->getBody()->isReadable());
    }

    /**
     * @backupGlobals enabled
     */
    public function testShouldBuildARequestWithTheHeadersFromTheServerParams(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/notify.php?payum_token=theHash';
        $_SERVER['HTTP_HOST'] = 'shop.example.com';
        $_SERVER['HTTP_STRIPE_SIGNATURE'] = 't=1,v1=abc';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_GET = [
            'payum_token' => 'theHash',
        ];

        $request = $this->buildContainer()->get(ServerRequestInterface::class);

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('t=1,v1=abc', $request->getHeaderLine('Stripe-Signature'));
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame('shop.example.com', $request->getHeaderLine('Host'));
        $this->assertSame('/notify.php?payum_token=theHash', (string) $request->getUri());
        $this->assertSame([
            'payum_token' => 'theHash',
        ], $request->getQueryParams());
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

    public function testShouldKnowIso4217AndCryptoCurrenciesOutOfTheBox(): void
    {
        $currencies = $this->buildContainer()->get(Currencies::class);

        $this->assertSame(2, $currencies->subunitFor(new Currency('USD')));
        $this->assertSame(0, $currencies->subunitFor(new Currency('JPY')));
        $this->assertSame(3, $currencies->subunitFor(new Currency('KWD')));
        $this->assertSame(8, $currencies->subunitFor(new Currency('BTC')));
    }

    public function testShouldFormatAndParseAgainstTheRegisteredCurrencies(): void
    {
        $container = $this->buildContainer([
            Currencies::class => new CurrencyList([
                'ETH' => 18,
            ]),
        ]);

        $money = $container->get(MoneyParser::class)->parse('1.5', new Currency('ETH'));

        $this->assertSame('1500000000000000000', $money->getAmount());
        $this->assertSame('1.500000000000000000', $container->get(MoneyFormatter::class)->format($money));
    }

    public function testShouldAnswerGetCurrencyForACurrencyIso4217DoesNotList(): void
    {
        $container = $this->buildContainer([
            Currencies::class => new CurrencyList([
                'ETH' => 18,
            ]),
        ]);

        $container->get(GetCurrencyAction::class)->execute($request = new GetCurrency('ETH'));

        $this->assertSame(18, $request->exp);
    }

    public function testShouldResolveDeprecatedHttplugMessageFactoryToAPsr17Factory(): void
    {
        if (interface_exists(MessageFactory::class)) {
            $this->markTestSkipped('php-http/message-factory is installed');
        }

        $definitions = (new CoreGatewayFactory())->configureContainer();

        $deprecations = $this->collectDeprecations(function () use ($definitions, &$messageFactory): void {
            $messageFactory = $definitions['httplug.message_factory']();
        });

        $this->assertInstanceOf(RequestFactoryInterface::class, $messageFactory);
        $this->assertContains(
            'Since payum/core 2.0.0: The "httplug.message_factory" config key is deprecated and will be removed in 3.0. Use "payum.http_message_factory", a Psr\Http\Message\RequestFactoryInterface, instead.',
            $deprecations
        );
    }

    public function testShouldResolveDeprecatedHttplugStreamFactoryToAPsr17Factory(): void
    {
        if (interface_exists(StreamFactory::class)) {
            $this->markTestSkipped('php-http/message-factory is installed');
        }

        $definitions = (new CoreGatewayFactory())->configureContainer();

        $deprecations = $this->collectDeprecations(function () use ($definitions, &$streamFactory): void {
            $streamFactory = $definitions['httplug.stream_factory']();
        });

        $this->assertInstanceOf(StreamFactoryInterface::class, $streamFactory);
        $this->assertContains(
            'Since payum/core 2.0.0: The "httplug.stream_factory" config key is deprecated and will be removed in 3.0. Use "payum.http_stream_factory", a Psr\Http\Message\StreamFactoryInterface, instead.',
            $deprecations
        );
    }

    public function testShouldResolveDeprecatedHttplugFactoriesToHttplugOnesWhenTheApplicationInstallsThem(): void
    {
        if (! interface_exists(MessageFactory::class) || ! interface_exists(StreamFactory::class)) {
            $this->markTestSkipped('php-http/message-factory is not installed');
        }

        $definitions = (new CoreGatewayFactory())->configureContainer();

        $this->collectDeprecations(function () use ($definitions, &$messageFactory, &$streamFactory): void {
            $messageFactory = $definitions['httplug.message_factory']();
            $streamFactory = $definitions['httplug.stream_factory']();
        });

        $this->assertInstanceOf(MessageFactory::class, $messageFactory);
        $this->assertInstanceOf(StreamFactory::class, $streamFactory);
    }

    public function testShouldFallBackToPsr17WhenHttplugFactoriesAreInstalledWithoutAnImplementation(): void
    {
        if (! interface_exists(MessageFactory::class) || ! interface_exists(StreamFactory::class)) {
            $this->markTestSkipped('php-http/message-factory is not installed');
        }

        $definitions = (new CoreGatewayFactory())->configureContainer();
        $strategies = ClassDiscovery::getStrategies();
        ClassDiscovery::setStrategies([Psr17OnlyDiscoveryStrategy::class]);

        try {
            $this->collectDeprecations(function () use ($definitions, &$messageFactory, &$streamFactory): void {
                $messageFactory = $definitions['httplug.message_factory']();
                $streamFactory = $definitions['httplug.stream_factory']();
            });
        } finally {
            ClassDiscovery::setStrategies($strategies);
        }

        $this->assertInstanceOf(RequestFactoryInterface::class, $messageFactory);
        $this->assertInstanceOf(StreamFactoryInterface::class, $streamFactory);
    }

    public function testShouldResolveDeprecatedHttplugClient(): void
    {
        $definitions = (new CoreGatewayFactory())->configureContainer();

        $deprecations = $this->collectDeprecations(function () use ($definitions, &$client): void {
            $client = $definitions['httplug.client'](new ArrayObject());
        });

        $this->assertInstanceOf(ClientInterface::class, $client);
        $this->assertContains(
            'Since payum/core 2.0.0: The "httplug.client" config key is deprecated and will be removed in 3.0. Use "payum.http_client", a Psr\Http\Client\ClientInterface, instead.',
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
            'Since payum/core 2.0.0: The Payum\Core\CoreGatewayFactory::create is deprecated and will be removed in 3.0. Implement the Payum\Core\DI\ContainerConfiguration interface instead.',
            $deprecations
        );
    }

    public function testCreateShouldReportNothingCoreCallsOnItsOwnBehalf(): void
    {
        $factory = new CoreGatewayFactory();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->create();
        });

        $this->assertSame([
            'Since payum/core 2.0.0: The Payum\Core\CoreGatewayFactory::create is deprecated and will be removed in 3.0. Implement the Payum\Core\DI\ContainerConfiguration interface instead.',
        ], $deprecations);
    }

    public function testCreateShouldReportABuildMethodASubclassOverridesOnce(): void
    {
        $factory = new CoreGatewayFactoryOverridingBuildActions();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->create();
            $factory->create();
        });

        $this->assertCount(1, array_keys($deprecations, sprintf(
            'Since payum/core 2.0.0: Overriding %s::buildActions() in %s is deprecated and will be removed in 3.0. Implement the %s interface instead.',
            CoreGatewayFactory::class,
            CoreGatewayFactoryOverridingBuildActions::class,
            ContainerConfiguration::class,
        ), true));
    }

    public function testCreateShouldReportAnApiConfigKeyTheCallerSets(): void
    {
        $factory = new CoreGatewayFactory();

        $deprecations = $this->collectDeprecations(static function () use ($factory): void {
            $factory->create([
                'payum.api.acme' => new stdClass(),
            ]);
        });

        $this->assertContains(
            'Since payum/core 2.0.0: The "payum.api.acme" config key is deprecated and will be removed in 3.0, along with every payum.api.* key. Take the api as a constructor argument on the action instead, and let the container inject it.',
            $deprecations
        );
        $this->assertSame([], preg_grep('#payum\.api\.http_client|Gateway::addApi#', $deprecations));
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
            'Since payum/core 2.0.0: The Payum\Core\CoreGatewayFactory::createConfig is deprecated and will be removed in 3.0. Implement the Payum\Core\DI\ContainerConfiguration interface instead.',
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

class Psr17OnlyDiscoveryStrategy implements DiscoveryStrategy
{
    /**
     * @return list<array{class: class-string, condition: class-string}>
     */
    public static function getCandidates($type): array
    {
        if (RequestFactoryInterface::class !== $type && StreamFactoryInterface::class !== $type) {
            return [];
        }

        return [[
            'class' => Psr17Factory::class,
            'condition' => Psr17Factory::class,
        ]];
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

class CoreGatewayFactoryOverridingBuildActions extends CoreGatewayFactory
{
    protected function buildActions(Gateway $gateway, ArrayObject $config): void
    {
        parent::buildActions($gateway, $config);
    }
}
