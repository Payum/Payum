<?php
namespace Payum\Core\Tests;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\PaymentFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithoutAnyOptions()
    {
        $factory = new PaymentFactory();

        $payment = $factory->create(array());

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeEmpty('apis', $payment);
        $this->assertAttributeNotEmpty('actions', $payment);

        $extensions = $this->readAttribute($payment, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithCustomApi()
    {
        $factory = new PaymentFactory();

        $payment = $factory->create(array(
            'payum.api' => new \stdClass(),
        ));

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeNotEmpty('apis', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentConfig()
    {
        $factory = new PaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);

        $this->assertInstanceOf('Twig_Environment', $config['twig.env']);
        $this->assertInstanceOf('Buzz\Client\ClientInterface', $config['buzz.client']);
        $this->assertInstanceOf('Payum\Core\Action\GetHttpRequestAction', $config['payum.action.get_http_request']);
        $this->assertInstanceOf('Payum\Core\Action\CaptureOrderAction', $config['payum.action.capture_order']);
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
    public function shouldAllowPrependAction()
    {
        $firstAction = $this->getMock('Payum\Core\Action\ActionInterface');
        $secondAction = $this->getMock('Payum\Core\Action\ActionInterface');

        $factory = new PaymentFactory();

        $payment = $factory->create(array(
            'payum.action.foo' => $firstAction,
            'payum.action.bar' => $secondAction,
        ));

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertSame($firstAction, $actions[0]);
        $this->assertSame($secondAction, $actions[1]);

        $payment = $factory->create(array(
            'payum.action.foo' => $firstAction,
            'payum.action.bar' => $secondAction,
            'payum.prepend_actions' => array(
                'payum.action.bar'
            )
        ));

        $actions = $this->readAttribute($payment, 'actions');
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

        $factory = new PaymentFactory();

        $payment = $factory->create(array(
            'payum.api.foo' => $firstApi,
            'payum.api.bar' => $secondApi,
        ));

        $apis = $this->readAttribute($payment, 'apis');
        $this->assertSame($firstApi, $apis[0]);
        $this->assertSame($secondApi, $apis[1]);

        $payment = $factory->create(array(
            'payum.api.foo' => $firstApi,
            'payum.api.bar' => $secondApi,
            'payum.prepend_apis' => array(
                'payum.api.bar'
            )
        ));

        $apis = $this->readAttribute($payment, 'apis');
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

        $factory = new PaymentFactory();

        $payment = $factory->create(array(
            'payum.extension.foo' => $firstExtension,
            'payum.extension.bar' => $secondExtension,
        ));

        $extensions = $this->readAttribute($this->readAttribute($payment, 'extensions'), 'extensions');
        $this->assertSame($firstExtension, $extensions[0]);
        $this->assertSame($secondExtension, $extensions[1]);

        $payment = $factory->create(array(
            'payum.extension.foo' => $firstExtension,
            'payum.extension.bar' => $secondExtension,
            'payum.prepend_extensions' => array(
                'payum.extension.bar'
            )
        ));

        $extensions = $this->readAttribute($this->readAttribute($payment, 'extensions'), 'extensions');
        $this->assertSame($secondExtension, $extensions[0]);
        $this->assertSame($firstExtension, $extensions[1]);
    }
}
