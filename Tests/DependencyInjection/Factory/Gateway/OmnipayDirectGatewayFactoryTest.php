<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayDirectGatewayFactory;
use Symfony\Component\HttpKernel\Kernel;

class OmnipayDirectGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayDirectGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayDirectGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new OmnipayDirectGatewayFactory;

        $this->assertEquals('omnipay_direct', $factory->getName());
    }

    /**
     * @test
     *
     * @dataProvider provideConfigs
     */
    public function shouldAllowAddConfiguration($config)
    {
        $factory = new OmnipayDirectGatewayFactory;

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

        //come from abstract gateway factory
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
        $factory = new OmnipayDirectGatewayFactory;

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
     * @expectedExceptionMessage The child node "options" at path "foo" must be configured.
     */
    public function thrownIfOptionsSectionMissing()
    {
        $factory = new OmnipayDirectGatewayFactory;

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
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new OmnipayDirectGatewayFactory;

        $container = new ContainerBuilder();

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'type' => 'PayPal_Express',
            'options' => array(
                'foo' => 'foo',
                'bar' => 'bar',
            ),
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.omnipay_direct.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));

        $gateway = $container->getDefinition($gatewayId);

        //guard
        $this->assertNotEmpty($gateway->getFactory());
        $this->assertNotEmpty($gateway->getArguments());

        $config = $gateway->getArgument(0);

        $this->assertEquals('aGatewayName', $config['payum.gateway_name']);
    }

    /**
     * @test
     */
    public function shouldLoadFactory()
    {
        $factory = new OmnipayDirectGatewayFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.omnipay_direct.factory'));

        $factoryService = $container->getDefinition('payum.omnipay_direct.factory');
        $this->assertEquals('Payum\OmnipayBridge\OmnipayDirectGatewayFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('factory_name' => 'omnipay_direct', 'human_name' => 'Omnipay Direct')),
            $factoryService->getTag('payum.gateway_factory')
        );

        $factoryConfig = $factoryService->getArgument(2);
        $this->assertEquals('omnipay_direct', $factoryConfig['payum.factory_name']);
        $this->assertArrayHasKey('payum.http_client', $factoryConfig);
        $this->assertArrayHasKey('twig.env', $factoryConfig);
        $this->assertArrayHasKey('payum.iso4217', $factoryConfig);
        $this->assertArrayHasKey('payum.template.layout', $factoryConfig);
        $this->assertArrayHasKey('payum.template.obtain_credit_card', $factoryConfig);

        $this->assertInstanceOf(Reference::class, $factoryService->getArgument(3));
        $this->assertEquals('payum.gateway_factory', (string) $factoryService->getArgument(3));
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new OmnipayDirectGatewayFactory;

        $container = new ContainerBuilder();

        $gatewayId = $factory->create($container, 'aGatewayName', array(
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
            $container->getDefinition($gatewayId),
            'addAction',
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addApi',
            new Reference('payum.api.bar')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
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
