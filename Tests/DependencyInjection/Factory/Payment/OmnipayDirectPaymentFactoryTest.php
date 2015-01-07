<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Payment;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayDirectPaymentFactory;

class OmnipayDirectPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractPaymentFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayDirectPaymentFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayDirectPaymentFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new OmnipayDirectPaymentFactory;

        $this->assertEquals('omnipay_direct', $factory->getName());
    }

    /**
     * @test
     *
     * @dataProvider provideConfigs
     */
    public function shouldAllowAddConfiguration($config)
    {
        $factory = new OmnipayDirectPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array($config));

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
        $factory = new OmnipayDirectPaymentFactory;

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
        $factory = new OmnipayDirectPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'type' => 'notSupportedGatewayType',
            'options' => array(),
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
        $factory = new OmnipayDirectPaymentFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'type' => 'PayPal_Express',
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentAndReturnItsId()
    {
        $factory = new OmnipayDirectPaymentFactory;

        $container = new ContainerBuilder();

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

        $this->assertEquals('payum.omnipay_direct.aPaymentName.payment', $paymentId);
        $this->assertTrue($container->hasDefinition($paymentId));
    }

    /**
     * @test
     */
    public function shouldLoadFactory()
    {
        $factory = new OmnipayDirectPaymentFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.omnipay_direct.factory'));

        $factoryService = $container->getDefinition('payum.omnipay_direct.factory');
        $this->assertEquals('Payum\OmnipayBridge\DirectPaymentFactory', $factoryService->getClass());
        $this->assertEquals(array(array('name' => 'omnipay_direct')), $factoryService->getTag('payum.payment_factory'));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(0));
        $this->assertEquals('payum.payment_factory', (string) $factoryService->getArgument(0));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new OmnipayDirectPaymentFactory;

        $container = new ContainerBuilder();

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

    public static function provideConfigs()
    {
        return array(
            array(
                array(
                    'type' => 'PayPal_Express',
                    'options' => array(
                        'foo' => 'foo',
                        'bar' => 'bar',
                    ),
                ),
            ),
            array(
                array(
                    'type' => '\Omnipay\PayPal\ExpressGateway',
                    'options' => array(
                        'foo' => 'foo',
                        'bar' => 'bar',
                    ),
                ),
            ),
        );
    }
}
