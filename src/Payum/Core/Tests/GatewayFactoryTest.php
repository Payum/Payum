<?php
namespace Payum\Core\Tests;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class GatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\GatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GatewayFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithoutAnyOptions()
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create(array());

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomApi()
    {
        $factory = new GatewayFactory();

        $gateway = $factory->create(array(
            'payum.api' => new \stdClass(),
        ));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new GatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf('Twig_Environment', $config['twig.env']);
        $this->assertInstanceOf('Buzz\Client\ClientInterface', $config['buzz.client']);
        $this->assertInstanceOf('Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction', $config['payum.action.get_http_request']);
        $this->assertInstanceOf('Payum\Core\Action\CapturePaymentAction', $config['payum.action.capture_payment']);
        $this->assertInstanceOf('Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction', $config['payum.action.execute_same_request_with_model_details']);
        $this->assertInstanceOf('Closure', $config['payum.action.render_template']);
        $this->assertInstanceOf(
            'Payum\Core\Bridge\Twig\Action\RenderTemplateAction',
            call_user_func_array($config['payum.action.render_template'], array(new ArrayObject($config)))
        );
        $this->assertInstanceOf('Payum\Core\Extension\EndlessCycleDetectorExtension', $config['payum.extension.endless_cycle_detector']);

        $this->assertEquals('@PayumCore/layout.html.twig', $config['payum.template.layout']);
        $this->assertEquals(array(), $config['payum.prepend_actions']);
        $this->assertEquals(array(), $config['payum.prepend_extensions']);
        $this->assertEquals(array(), $config['payum.prepend_apis']);
        $this->assertEquals(array(), $config['payum.default_options']);
        $this->assertEquals(array(), $config['payum.required_options']);
    }

    /**
     * @test
     */
    public function shouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new GatewayFactory(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

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
        $firstAction = $this->getMock('Payum\Core\Action\ActionInterface');
        $secondAction = $this->getMock('Payum\Core\Action\ActionInterface');

        $factory = new GatewayFactory();

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

        $factory = new GatewayFactory();

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
        $firstExtension = $this->getMock('Payum\Core\Extension\ExtensionInterface');
        $secondExtension = $this->getMock('Payum\Core\Extension\ExtensionInterface');

        $factory = new GatewayFactory();

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
}
