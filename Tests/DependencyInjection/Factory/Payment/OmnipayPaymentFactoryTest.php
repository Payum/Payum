<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;

class OmnipayPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (false == class_exists('Omnipay\Common\GatewayFactory')) {
            $this->markTestSkipped('The omnipay lib required to run these tests');
        }
    }
    
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new OmnipayPaymentFactory;

        $this->assertEquals('omnipay', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new OmnipayPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            )
        )));

        $this->assertArrayHasKey('type', $config);

        $this->assertArrayHasKey('options', $config);
        
        $this->assertArrayHasKey('foo', $config['options']);
        $this->assertEquals('foo', $config['options']['foo']);
        
        $this->assertArrayHasKey('bar', $config['options']);
        $this->assertEquals('bar', $config['options']['bar']);

        //come from abstract payment factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "type" at path "foo" must be configured.
     */
    public function thrownIfTypeSectionMissing()
    {
        $factory = new OmnipayPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "foo": Given type notSupportedGatewayType is not supported.
     */
    public function thrownIfTypeNotSupportedByOmnipay()
    {
        $factory = new OmnipayPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'type' => 'notSupportedGatewayType',
            'options' => array()
        )));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "options" at path "foo" must be configured.
     */
    public function thrownIfApiOptionsSectionMissing()
    {
        $factory = new OmnipayPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'type' => 'PayPal_Express'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new OmnipayPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.context.aContextName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new OmnipayPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array('payum.action.foo'),
            'apis' => array('payum.api.bar'),
            'extensions' => array('payum.extension.ololo'),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId), 
            'addAction', 
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.api.bar')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addExtension',
            new Reference('payum.extension.ololo')
        );
    }

    /**
     * @test
     */
    public function shouldDecorateBasicApiDefinitionAndAddItToPayment()
    {
        $factory = new OmnipayPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.gateway'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addApi',
            new Reference('payum.context.aContextName.gateway')
        );
    }

    /**
     * @test
     */
    public function shouldDecorateBasicCaptureActionDefinitionAndAddItToPayment()
    {
        $factory = new OmnipayPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.action.capture'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.context.aContextName.action.capture')
        );
    }

    /**
     * @test
     */
    public function shouldDecorateBasicStatusActionDefinitionAndAddItToPayment()
    {
        $factory = new OmnipayPaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aContextName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertTrue($container->hasDefinition('payum.context.aContextName.action.status'));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($paymentId),
            'addAction',
            new Reference('payum.context.aContextName.action.status')
        );
    }

    protected function assertDefinitionContainsMethodCall(Definition $serviceDefinition, $expectedMethod, $expectedFirstArgument)
    {
        foreach ($serviceDefinition->getMethodCalls() as $methodCall) {
            if ($expectedMethod == $methodCall[0] && $expectedFirstArgument == $methodCall[1][0]) {
                return;
            }
        }

        $this->fail(sprintf(
            'Failed assert that service (Class: %s) has method %s been called with first argument %s',
            $serviceDefinition->getClass(),
            $expectedMethod,
            $expectedFirstArgument
        ));
    }
}