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
use Payum\Core\Storage\StorageInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CoreGatewayFactoryTest extends TestCase
{
    public function testShouldImplementCoreGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(CoreGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    public function testShouldAllowCreateGatewayWithoutAnyOptions()
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create([]);

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    public function testShouldAlwaysAddHttpClientAsApi()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('payum.api.http_client', $config);
        $this->assertInstanceOf(\Closure::class, $config['payum.api.http_client']);

        $this->assertSame($config['payum.http_client'], $config['payum.api.http_client'](new ArrayObject($config)));
    }

    public function testShouldCreateDefaultHttplugMessageFactory()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.message_factory', $config);
        $this->assertInstanceOf(\Closure::class, $config['httplug.message_factory']);
        $config['httplug.message_factory'] = call_user_func($config['httplug.message_factory'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(MessageFactory::class, $config['httplug.message_factory']);
    }

    public function testShouldCreateDefaultHttplugStreamFactory()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig([]);
        $this->assertArrayHasKey('httplug.stream_factory', $config);
        $this->assertInstanceOf(\Closure::class, $config['httplug.stream_factory']);
        $config['httplug.stream_factory'] = call_user_func($config['httplug.stream_factory'], ArrayObject::ensureArrayObject($config));
        $this->assertInstanceOf(StreamFactory::class, $config['httplug.stream_factory']);
    }

    public function testShouldCreateDefaultHttplugClient()
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

    public function testShouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new CoreGatewayFactory();

        $gateway = $factory->create(array(
            'payum.api' => new \stdClass(),
        ));

        $this->assertInstanceOf(Gateway::class, $gateway);
    }

    public function testShouldAllowCreateGatewayConfig()
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

        $this->assertSame('@PayumCore/layout.html.twig', $config['payum.template.layout']);
        $this->assertSame([], $config['payum.prepend_actions']);
        $this->assertSame([], $config['payum.prepend_extensions']);
        $this->assertSame([], $config['payum.prepend_apis']);
        $this->assertSame([], $config['payum.default_options']);
        $this->assertSame([], $config['payum.required_options']);
    }

    public function testShouldConfigurePaths()
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

    public function testShouldConfigurePathsPlusExtraOne()
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
        $this->assertSame('FooPath', $config['payum.paths']['FooNamespace']);
    }

    public function testShouldConfigureTwigEnvironmentGatewayConfig()
    {
        $factory = new CoreGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf(\Closure::class, $config['twig.env']);

        $twig = call_user_func($config['twig.env'], ArrayObject::ensureArrayObject($config));

        $this->assertInstanceOf(Environment::class, $twig);
    }

    public function testShouldConfigureRenderTemplateAction()
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

        $this->assertSame($twig, $config['twig.env']);
    }

    public function testShouldConfigureGetTokenActionIfTokenStorageSet()
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

        $this->assertSame($tokenStorageMock, $config['payum.security.token_storage']);
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new CoreGatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    public function testShouldAllowPrependAction()
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

    public function testShouldAllowPrependApi()
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

    public function testShouldAllowPrependExtensions()
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

    public function testShouldNotAllowGlobalFunctionsAsGatewayConfig()
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
