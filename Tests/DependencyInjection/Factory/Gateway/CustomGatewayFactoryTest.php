<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\CustomGatewayFactory;
use Symfony\Component\HttpKernel\Kernel;

class CustomGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\CustomGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CustomGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new CustomGatewayFactory;

        $this->assertEquals('custom', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new CustomGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array()));

        //come from abstract gateway factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     */
    public function shouldAllowAddConfigurationWithCustomGatewayService()
    {
        $factory = new CustomGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'service' => 'foo.gateway.service'
        )));

        $this->assertArrayHasKey('service', $config);
        $this->assertEquals('foo.gateway.service', $config['service']);

        //come from abstract gateway factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new CustomGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.custom.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));
        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\Definition',
            $container->getDefinition($gatewayId)
        );

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
        $factory = new CustomGatewayFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.custom.factory'));

        $factoryService = $container->getDefinition('payum.custom.factory');
        $this->assertEquals('Payum\Core\GatewayFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('factory_name' => 'custom', 'human_name' => 'Custom')),
            $factoryService->getTag('payum.gateway_factory')
        );

        $factoryConfig = $factoryService->getArgument(0);
        $this->assertEquals('custom', $factoryConfig['payum.factory_name']);
        $this->assertArrayHasKey('payum.http_client', $factoryConfig);
        $this->assertArrayHasKey('twig.env', $factoryConfig);
        $this->assertArrayHasKey('payum.iso4217', $factoryConfig);
        $this->assertArrayHasKey('payum.template.layout', $factoryConfig);
        $this->assertArrayHasKey('payum.template.obtain_credit_card', $factoryConfig);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(1));
        $this->assertEquals('payum.gateway_factory', (string) $factoryService->getArgument(1));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsIdWhenCustomGatewayServiceSet()
    {
        $factory = new CustomGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'service' => 'foo.gateway.service',
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.custom.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));
        $this->assertInstanceOf(
            'Symfony\Component\DependencyInjection\DefinitionDecorator',
            $container->getDefinition($gatewayId)
        );
    }

    /**
     * @test
     */
    public function shouldCallParentsCreateMethod()
    {
        $factory = new CustomGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
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
}