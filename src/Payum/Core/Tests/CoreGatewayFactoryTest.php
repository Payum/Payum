<?php
namespace Payum\Core\Tests;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Http\Message\StreamFactory;
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
use Payum\Core\HttpClientInterface;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CoreGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementCoreGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CoreGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithoutAnyOptions()
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([]);

        $this->assertInstanceOf(Gateway::class, $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAlwaysAddHttpClientAsApi()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('payum.api.http_client', $config);
        $this->assertInstanceOf(\Closure::class, $config['payum.api.http_client']);

        $this->assertSame($config['payum.http_client'], $config['payum.api.http_client'](new ArrayObject($config)));
    }

    /**
     * @test
     */
    public function shouldCreateDefaultHttplugMessageFactory()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.message_factory', $config);
        $this->assertInstanceOf(\Closure::class, $config['httplug.message_factory']);
        $config['httplug.message_factory'] = call_user_func($config['httplug.message_factory'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(MessageFactory::class, $config['httplug.message_factory']);
    }

    /**
     * @test
     */
    public function shouldCreateDefaultHttplugStreamFactory()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.stream_factory', $config);
        $this->assertInstanceOf(\Closure::class, $config['httplug.stream_factory']);
        $config['httplug.stream_factory'] = call_user_func($config['httplug.stream_factory'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(StreamFactory::class, $config['httplug.stream_factory']);
    }

    /**
     * @test
     */
    public function shouldCreateDefaultHttplugClient()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.client', $config);
        $this->assertInstanceOf(\Closure::class, $config['httplug.client']);

        $config['httplug.message_factory'] = call_user_func($config['httplug.message_factory'], ArrayObject::ensureArrayObject($config));
        $config['httplug.stream_factory'] = call_user_func($config['httplug.stream_factory'], ArrayObject::ensureArrayObject($config));
        $config['httplug.client'] = call_user_func($config['httplug.client'], ArrayObject::ensureArrayObject($config));

        $this->assertInstanceOf(HttpClient::class, $config['httplug.client']);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create(array(
            'payum.api' => new \stdClass(),
        ));

        $this->assertInstanceOf(Gateway::class, $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(\Closure::class, $config['payum.http_client']);
        $this->assertInstanceOf(GetHttpRequestAction::class, $config['payum.action.get_http_request']);
        $this->assertInstanceOf(CapturePaymentAction::class, $config['payum.action.capture_payment']);
        $this->assertInstanceOf(PayoutPayoutAction::class, $config['payum.action.payout_payout']);
        $this->assertInstanceOf(ExecuteSameRequestWithModelDetailsAction::class, $config['payum.action.execute_same_request_with_model_details']);
        $this->assertInstanceOf(\Closure::class, $config['payum.action.render_template']);
        $this->assertInstanceOf(EndlessCycleDetectorExtension::class, $config['payum.extension.endless_cycle_detector']);

        $this->assertEquals('@PayumCore/layout.html.twig', $config['payum.template.layout']);
        $this->assertEquals([], $config['payum.prepend_actions']);
        $this->assertEquals([], $config['payum.prepend_extensions']);
        $this->assertEquals([], $config['payum.prepend_apis']);
        $this->assertEquals([], $config['payum.default_options']);
        $this->assertEquals([], $config['payum.required_options']);
    }

    /**
     * @test
     */
    public function shouldConfigurePaths()
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

    /**
     * @test
     */
    public function shouldConfigurePathsPlusExtraOne()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([
            'payum.paths' => ['FooNamespace' => 'FooPath']
        ]);

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertIsArray($config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertFileExists($config['payum.paths']['PayumCore']);

        $this->assertArrayHasKey('FooNamespace', $config['payum.paths']);
        $this->assertEquals('FooPath', $config['payum.paths']['FooNamespace']);
    }

    /**
     * @test
     */
    public function shouldConfigureTwigEnvironmentGatewayConfig()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(\Closure::class, $config['twig.env']);

        $twig = call_user_func($config['twig.env'], ArrayObject::ensureArrayObject($config));

        $this->assertInstanceOf(Environment::class, $twig);
    }

    /**
     * @test
     */
    public function shouldConfigureRenderTemplateAction()
    {
        $factory = new CoreGatewayFactory();

        $twig = new Environment(new FilesystemLoader());

        $config = $factory->createConfig([
            'twig.env' => $twig,
        ]);

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertSame($twig, $config['twig.env']);

        $this->assertInstanceOf(\Closure::class, $config['payum.action.render_template']);

        $action = call_user_func($config['payum.action.render_template'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(RenderTemplateAction::class, $action);
        $this->assertAttributeSame($twig, 'twig', $action);

        $this->assertSame($twig, $config['twig.env']);
    }

    /**
     * @test
     */
    public function shouldConfigureGetTokenActionIfTokenStorageSet()
    {
        $factory = new CoreGatewayFactory();

        $tokenStorageMock = $this->createMock(StorageInterface::class);

        $config = $factory->createConfig([
            'payum.security.token_storage' => $tokenStorageMock,
        ]);

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(\Closure::class, $config['payum.action.get_token']);

        $action = call_user_func($config['payum.action.get_token'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(GetTokenAction::class, $action);
        $this->assertAttributeSame($tokenStorageMock, 'tokenStorage', $action);

        $this->assertSame($tokenStorageMock, $config['payum.security.token_storage']);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new CoreGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    /**
     * @test
     */
    public function shouldAllowPrependAction()
    {
        $firstAction = $this->createMock('Payum\Core\Action\ActionInterface');
        $secondAction = $this->createMock('Payum\Core\Action\ActionInterface');

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create(array(
            'payum.action.foo' => $firstAction,
            'payum.action.bar' => $secondAction,
        ));

        $actions = $this->readAttribute($gateway, 'actions');
        $this->assertSame($firstAction, $actions[0]);
        $this->assertSame($secondAction, $actions[1]);

        $gateway = $factory->create(array(
            'payum.action.foo' => $firstAction,
            'payum.action.bar' => $secondAction,
            'payum.prepend_actions' => array(
                'payum.action.bar'
            )
        ));

        $actions = $this->readAttribute($gateway, 'actions');
        $this->assertSame($secondAction, $actions[0]);
        $this->assertSame($firstAction, $actions[1]);
    }

    /**
     * @test
     */
    public function shouldAllowPrependApi()
    {
        $firstApi = new \stdClass();
        $secondApi = new \stdClass();

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create(array(
            'payum.api.foo' => $firstApi,
            'payum.api.bar' => $secondApi,
        ));

        $apis = $this->readAttribute($gateway, 'apis');
        $this->assertSame($firstApi, $apis[0]);
        $this->assertSame($secondApi, $apis[1]);

        $gateway = $factory->create(array(
            'payum.api.foo' => $firstApi,
            'payum.api.bar' => $secondApi,
            'payum.prepend_apis' => array(
                'payum.api.bar'
            )
        ));

        $apis = $this->readAttribute($gateway, 'apis');
        $this->assertSame($secondApi, $apis[0]);
        $this->assertSame($firstApi, $apis[1]);
    }

    /**
     * @test
     */
    public function shouldAllowPrependExtensions()
    {
        $firstExtension = $this->createMock(ExtensionInterface::class);
        $secondExtension = $this->createMock(ExtensionInterface::class);

        $factory = new CoreGatewayFactory();

        $gateway = $factory->create(array(
            'payum.extension.foo' => $firstExtension,
            'payum.extension.bar' => $secondExtension,
        ));

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');
        $this->assertSame($firstExtension, $extensions[0]);
        $this->assertSame($secondExtension, $extensions[1]);

        $gateway = $factory->create(array(
            'payum.extension.foo' => $firstExtension,
            'payum.extension.bar' => $secondExtension,
            'payum.prepend_extensions' => array(
                'payum.extension.bar'
            )
        ));

        $extensions = $this->readAttribute($this->readAttribute($gateway, 'extensions'), 'extensions');
        $this->assertSame($secondExtension, $extensions[0]);
        $this->assertSame($firstExtension, $extensions[1]);
    }

    /**
     * @test
     */
    public function shouldNotAllowGlobalFunctionsAsGatewayConfig()
    {
        $factory = new CoreGatewayFactory();

        $factory->create(array(
            'hash' => 'sha1',
            'verify' => function ($config) {
                $this->assertSame('sha1', $config['hash']);
            },
        ));
    }
}
