<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayOffsitePaymentFactory;

class OmnipayOffsitePaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfOmnipayDirectPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayOffsitePaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayDirectPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayOffsitePaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new OmnipayOffsitePaymentFactory;

        $this->assertEquals('omnipay_offsite', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new OmnipayOffsitePaymentFactory;

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
        $factory = new OmnipayOffsitePaymentFactory;

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
        $factory = new OmnipayOffsitePaymentFactory;

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
    public function thrownIfOptionsSectionMissing()
    {
        $factory = new OmnipayOffsitePaymentFactory;

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
        $factory = new OmnipayOffsitePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.omnipay_offsite.aPaymentName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldLoadFactory()
    {
        $factory = new OmnipayOffsitePaymentFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.omnipay_offsite.factory'));

        $factoryService = $container->getDefinition('payum.omnipay_offsite.factory');
        $this->assertEquals('Payum\OmnipayBridge\OffsitePaymentFactory', $factoryService->getClass());
        $this->assertEquals(array(array('name' => 'omnipay_offsite')), $factoryService->getTag('payum.payment_factory'));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(0));
        $this->assertEquals('payum.payment_factory', (string) $factoryService->getArgument(0));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new OmnipayOffsitePaymentFactory;

        $container = new ContainerBuilder;

        $paymentId = $factory->create($container, 'aPaymentName', array(
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