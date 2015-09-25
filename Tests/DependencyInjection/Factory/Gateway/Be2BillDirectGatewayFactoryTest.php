<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillDirectGatewayFactory;
use Symfony\Component\HttpKernel\Kernel;

class Be2BillDirectGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractGatewayFactory()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillDirectGatewayFactory');

        $this->assertTrue($rc->isSubclassOf('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Be2BillDirectGatewayFactory;
    }

    /**
     * @test
     */
    public function shouldAllowGetName()
    {
        $factory = new Be2BillDirectGatewayFactory;

        $this->assertEquals('be2bill_direct', $factory->getName());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = new Be2BillDirectGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $config = $processor->process($tb->buildTree(), array(array(
            'identifier' => 'anIdentifier',
            'password' => 'aPassword',
        )));

        $this->assertArrayHasKey('identifier', $config);
        $this->assertEquals('anIdentifier', $config['identifier']);
        
        $this->assertArrayHasKey('password', $config);
        $this->assertEquals('aPassword', $config['password']);

        $this->assertArrayHasKey('sandbox', $config);
        $this->assertTrue($config['sandbox']);

        //come from abstract gateway factory
        $this->assertArrayHasKey('actions', $config);
        $this->assertArrayHasKey('apis', $config);
        $this->assertArrayHasKey('extensions', $config);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "identifier" at path "foo" must be configured.
     */
    public function thrownIfIdentifierOptionNotSet()
    {
        $factory = new Be2BillDirectGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array()));
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "password" at path "foo" must be configured.
     */
    public function thrownIfPasswordOptionNotSet()
    {
        $factory = new Be2BillDirectGatewayFactory;

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array(array(
            'identifier' => 'anIdentifier'
        )));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = new Be2BillDirectGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'identifier' => 'anIdentifier',
            'password' => 'aPassword',
            'sandbox' => true,
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.be2bill_direct.aGatewayName.gateway', $gatewayId);
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
        $factory = new Be2BillDirectGatewayFactory;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.be2bill_direct.factory'));

        $factoryService = $container->getDefinition('payum.be2bill_direct.factory');
        $this->assertEquals('Payum\Be2Bill\Be2BillDirectGatewayFactory', $factoryService->getClass());
        $this->assertEquals(
            array(array('factory_name' => 'be2bill_direct', 'human_name' => 'Be2bill Direct')),
            $factoryService->getTag('payum.gateway_factory')
        );

        $factoryConfig = $factoryService->getArgument(0);
        $this->assertEquals('be2bill_direct', $factoryConfig['payum.factory_name']);
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
    public function shouldCallParentsCreateMethod()
    {
        $factory = new Be2BillDirectGatewayFactory;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'identifier' => 'anIdentifier',
            'password' => 'aPassword',
            'sandbox' => true,
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