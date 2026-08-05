<?php

namespace Payum\Core\Tests;

use Closure;
use DI\Container;
use Http\Message\RequestFactory;
use Http\Message\StreamFactory;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\CapturePaymentAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetTokenAction;
use Payum\Core\Action\PayoutPayoutAction;
use Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Twig\Action\RenderTemplateAction;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use ReflectionClass;
use stdClass;
use Twig\Environment;
use Twig\Loader\ChainLoader;

class CoreGatewayFactoryTest extends TestCase
{
    public function testShouldImplementCoreGatewayFactoryInterface(): void
    {
        $rc = new ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    public function testShouldAllowCreateGatewayWithoutAnyOptions(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([]);

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    public function testShouldAlwaysAddHttpClientAsApi(): void
    {
        $factory = new CoreGatewayFactory();

        $container = $this->getContainer();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('payum.api.http_client', $config);
        $this->assertInstanceOf(Closure::class, $config['payum.api.http_client']);

        $this->assertEquals($config['payum.http_client']($container), $config['payum.api.http_client']($container));
    }

    /**
     * @group legacy
     */
    public function testShouldCreateDefaultHttplugMessageFactory(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.message_factory', $config);
        $this->assertInstanceOf(RequestFactory::class, $config['httplug.message_factory']());
    }

    /**
     * @group legacy
     */
    public function testShouldCreateDefaultHttplugStreamFactory(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.stream_factory', $config);
        $this->assertInstanceOf(StreamFactory::class, $config['httplug.stream_factory']());
    }

    public function testShouldCreateDefaultHttMessageFactory(): void
    {
        $container = $this->getContainer();

        $this->assertTrue($container->has('payum.http_message_factory'));
        // createConfig() now returns resolved values, not closures
        $this->assertInstanceOf(RequestFactoryInterface::class, $container->get('payum.http_message_factory'));
    }

    public function testShouldCreateDefaultHttStreamFactory(): void
    {
        $container = $this->getContainer();
        $this->assertTrue($container->has('payum.http_stream_factory'));
        // createConfig() now returns resolved values, not closures
        $this->assertInstanceOf(StreamFactoryInterface::class, $container->get('payum.http_stream_factory'));
    }

    public function testShouldCreateDefaultHttplugClient(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.client', $config);

        // httplug.client is a closure that expects ArrayObject, so it won't be auto-resolved
        $this->assertInstanceOf(Closure::class, $config['httplug.client']);

        // Resolve dependencies (may already be resolved by createConfig)
        if ($config['httplug.message_factory'] instanceof Closure) {
            $config['httplug.message_factory'] = call_user_func($config['httplug.message_factory'], ArrayObject::ensureArrayObject($config));
        }
        if ($config['httplug.stream_factory'] instanceof Closure) {
            $config['httplug.stream_factory'] = call_user_func($config['httplug.stream_factory'], ArrayObject::ensureArrayObject($config));
        }
        $config['httplug.client'] = call_user_func($config['httplug.client'], ArrayObject::ensureArrayObject($config));

        $this->assertInstanceOf(ClientInterface::class, $config['httplug.client']);
    }

    public function testShouldAllowCreateGatewayWithCustomApi(): void
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.api' => new stdClass(),
        ]);

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    public function testShouldAllowCreateGatewayConfig(): void
    {
        $factory = new CoreGatewayFactory();

        $container = $this->getContainer();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(Closure::class, $config['payum.http_client']);
        $this->assertInstanceOf(GetHttpRequestAction::class, $config['payum.action.get_http_request']($container));
        $this->assertInstanceOf(CapturePaymentAction::class, $config['payum.action.capture_payment']($container));
        $this->assertInstanceOf(PayoutPayoutAction::class, $config['payum.action.payout_payout']($container));
        $this->assertInstanceOf(ExecuteSameRequestWithModelDetailsAction::class, $config['payum.action.execute_same_request_with_model_details']($container));
        $this->assertInstanceOf(Closure::class, $config['payum.action.render_template']);
        $this->assertInstanceOf(EndlessCycleDetectorExtension::class, $config['payum.extension.endless_cycle_detector']($container));

        $this->assertSame('@PayumCore/layout.html.twig', $config['payum.template.layout']);
        $this->assertSame([], $config['payum.prepend_actions']);
        $this->assertSame([], $config['payum.prepend_extensions']);
        $this->assertSame([], $config['payum.prepend_apis']);
        $this->assertSame([], $config['payum.default_options']);
        $this->assertSame([], $config['payum.required_options']);
    }

    public function testShouldConfigurePaths(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertFileExists($config['payum.paths']['PayumCore']);
    }

    public function testShouldConfigurePathsPlusExtraOne(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([
            'payum.paths' => [
                'FooNamespace' => 'FooPath',
            ],
        ]);

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertFileExists($config['payum.paths']['PayumCore']);

        $this->assertArrayHasKey('FooNamespace', $config['payum.paths']);
        $this->assertSame('FooPath', $config['payum.paths']['FooNamespace']);
    }

    public function testShouldConfigureTwigEnvironmentGatewayConfig(): void
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(Environment::class, $config['twig.env']());
    }

    public function testShouldConfigureRenderTemplateAction(): void
    {
        $factory = new CoreGatewayFactory();

        $container = $this->getContainer();

        $twig = new Environment(new ChainLoader());

        $config = $factory->createConfig([
            'twig.env' => $twig,
        ]);

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertSame($twig, $config['twig.env']);

        $this->assertInstanceOf(Closure::class, $config['payum.action.render_template']);

        $action = call_user_func($config['payum.action.render_template'], $container);
        $this->assertInstanceOf(RenderTemplateAction::class, $action);

        $this->assertSame($twig, $config['twig.env']);
        $this->assertEquals($twig, $container->get('twig.env'));
    }

    public function testShouldConfigureGetTokenActionIfTokenStorageSet(): void
    {
        $factory = new CoreGatewayFactory();

        $tokenStorageMock = $this->createMock(StorageInterface::class);

        $config = $factory->createConfig([
            'payum.security.token_storage' => $tokenStorageMock,
        ]);
        $container = $this->getContainer($config);

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(Closure::class, $config['payum.action.get_token']);

        $action = call_user_func($config['payum.action.get_token'], $container);
        $this->assertInstanceOf(GetTokenAction::class, $action);

        $this->assertSame($tokenStorageMock, $config['payum.security.token_storage']);
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(): void
    {
        $factory = new CoreGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    /**
     * NOTE: The "@group legacy" annotation is only added since we have some deprecated config options.
     * The annotation can be removed once the deprecated configs has been removed in 3.0
     *
     * @group legacy
     */
    public function testShouldAllowPrependAction(): void
    {
        $firstAction = $this->createMock(ActionInterface::class);
        $secondAction = $this->createMock(ActionInterface::class);

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.action.foo' => $firstAction,
            'payum.action.bar' => $secondAction,
        ]);

        $actions = $this->readAttribute($gateway, 'actions');

        $this->assertEquals($firstAction, $actions[0]);
        $this->assertEquals($secondAction, $actions[1]);

        $gateway = $factory->create([
            'payum.action.foo' => $firstAction,
            'payum.action.bar' => $secondAction,
            'payum.prepend_actions' => [
                'payum.action.bar',
            ],
        ]);

        $actions = $this->readAttribute($gateway, 'actions');
        $this->assertEquals($secondAction, $actions[0]);
        $this->assertEquals($firstAction, $actions[1]);
    }

    /**
     * NOTE: The "@group legacy" annotation is only added since we have some deprecated config options.
     * The annotation can be removed once the deprecated configs has been removed in 3.0
     *
     * @group legacy
     */
    public function testShouldAllowPrependApi(): void
    {
        $firstApi = new stdClass();
        $secondApi = new stdClass();

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.api.foo' => $firstApi,
            'payum.api.bar' => $secondApi,
        ]);

        $apis = $this->readAttribute($gateway, 'apis');
        $this->assertEquals($firstApi, $apis[0]);
        $this->assertEquals($secondApi, $apis[1]);

        $gateway = $factory->create([
            'payum.api.foo' => $firstApi,
            'payum.api.bar' => $secondApi,
            'payum.prepend_apis' => [
                'payum.api.bar',
            ],
        ]);

        $apis = $this->readAttribute($gateway, 'apis');
        $this->assertEquals($secondApi, $apis[0]);
        $this->assertEquals($firstApi, $apis[1]);
    }

    /**
     * NOTE: The "@group legacy" annotation is only added since we have some deprecated config options.
     * The annotation can be removed once the deprecated configs has been removed in 3.0
     *
     * @group legacy
     */
    public function testShouldAllowPrependExtensions(): void
    {
        $firstExtension = $this->createMock(ExtensionInterface::class);
        $secondExtension = $this->createMock(ExtensionInterface::class);

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([
            'payum.extension.foo' => $firstExtension,
            'payum.extension.bar' => $secondExtension,
        ]);

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');
        $this->assertEquals($firstExtension, $extensions[0]);
        $this->assertEquals($secondExtension, $extensions[1]);

        $gateway = $factory->create([
            'payum.extension.foo' => $firstExtension,
            'payum.extension.bar' => $secondExtension,
            'payum.prepend_extensions' => [
                'payum.extension.bar',
            ],
        ]);

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');
        $this->assertEquals($secondExtension, $extensions[0]);
        $this->assertEquals($firstExtension, $extensions[1]);
    }

    public function testShouldNotAllowGlobalFunctionsAsGatewayConfig(): void
    {
        $factory = new CoreGatewayFactory();

        $factory->create([
            'hash' => 'sha1',
            'verify' => function ($config): void {
                $this->assertSame('sha1', $config['hash']);
            },
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getContainer(array $config = []): ContainerInterface
    {
        $factory = new CoreGatewayFactory();

        return new Container($factory->createConfig($config));
    }
}
