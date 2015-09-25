<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Factory\Gateway;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory;
use Symfony\Component\HttpKernel\Kernel;

class AbstractGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory');
        
        $this->assertTrue($rc->implementsInterface('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\GatewayFactoryInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowAddConfiguration()
    {
        $factory = $this->createAbstractGatewayFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');
        
        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        $processor->process($tb->buildTree(), array());
    }

    /**
     * @test
     */
    public function shouldAllowConfigureCustomActions()
    {
        $factory = $this->createAbstractGatewayFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();
        
        $config = $processor->process($tb->buildTree(), array());        
        $this->assertArrayHasKey('actions', $config);
        $this->assertEmpty($config['actions']);

        $config = $processor->process($tb->buildTree(), array(array(
            'actions' => array(
                'payum.action.foo',
                'payum.action.bar'
            ))
        ));
        $this->assertArrayHasKey('actions', $config);
        $this->assertContains('payum.action.foo', $config['actions']);
        $this->assertContains('payum.action.bar', $config['actions']);
    }

    /**
     * @test
     */
    public function shouldAllowConfigureCustomApis()
    {
        $factory = $this->createAbstractGatewayFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array());
        $this->assertArrayHasKey('apis', $config);
        $this->assertEmpty($config['apis']);

        $config = $processor->process($tb->buildTree(), array(array(
            'apis' => array(
                'payum.api.foo',
                'payum.api.bar'
            ))
        ));
        $this->assertArrayHasKey('apis', $config);
        $this->assertContains('payum.api.foo', $config['apis']);
        $this->assertContains('payum.api.bar', $config['apis']);
    }

    /**
     * @test
     */
    public function shouldAllowConfigureCustomExtensions()
    {
        $factory = $this->createAbstractGatewayFactory();

        $tb = new TreeBuilder();
        $rootNode = $tb->root('foo');

        $factory->addConfiguration($rootNode);

        $processor = new Processor();

        $config = $processor->process($tb->buildTree(), array());
        $this->assertArrayHasKey('extensions', $config);
        $this->assertEmpty($config['extensions']);

        $config = $processor->process($tb->buildTree(), array(array(
            'extensions' => array(
                'payum.extension.foo',
                'payum.extension.bar'
            ))
        ));
        $this->assertArrayHasKey('extensions', $config);
        $this->assertContains('payum.extension.foo', $config['extensions']);
        $this->assertContains('payum.extension.bar', $config['extensions']);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayAndReturnItsId()
    {
        $factory = $this->createAbstractGatewayFactory();
        $factory
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));
        
        $this->assertEquals('payum.foo.aGatewayName.gateway', $gatewayId);
        $this->assertTrue($container->hasDefinition($gatewayId));
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithExpectedConfig()
    {
        $factory = $this->createAbstractGatewayFactory();
        $factory
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertEquals('payum.foo.aGatewayName.gateway', $gatewayId);

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
    public function shouldLoadFactoryAndTemplateParameters()
    {
        $factory = $this->createAbstractGatewayFactory();
        $factory
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo')
        ;

        $container = new ContainerBuilder;

        $factory->load($container);

        $this->assertTrue($container->hasDefinition('payum.foo.factory'));

        $factoryService = $container->getDefinition('payum.foo.factory');
        $this->assertEquals('Payum\Core\GatewayFactory', $factoryService->getClass());
        $this->assertEquals(array(array('factory_name' => 'foo', 'human_name' => 'Foo')), $factoryService->getTag('payum.gateway_factory'));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $factoryService->getArgument(1));
        $this->assertEquals('payum.gateway_factory', (string) $factoryService->getArgument(1));

        $this->assertEquals('@PayumCore\layout.html.twig', $container->getParameter('payum.template.layout'));
        $this->assertEquals('@PayumSymfonyBridge\obtainCreditCard.html.twig', $container->getParameter('payum.template.obtain_credit_card'));
    }

    /**
     * @test
     */
    public function shouldAddCustomActions()
    {
        $factory = $this->createAbstractGatewayFactory();

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(
                'payum.action.foo',
                'payum.action.bar',
            ),
            'apis' => array(),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addAction', 
            new Reference('payum.action.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addAction',
            new Reference('payum.action.bar')
        );
    }

    /**
     * @test
     */
    public function shouldAddCustomApis()
    {
        $factory = $this->createAbstractGatewayFactory();

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(
                'payum.api.foo',
                'payum.api.bar',
            ),
            'extensions' => array(),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addApi',
            new Reference('payum.api.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addApi',
            new Reference('payum.api.bar')
        );
    }

    /**
     * @test
     */
    public function shouldAddCustomExtensions()
    {
        $factory = $this->createAbstractGatewayFactory();

        $container = new ContainerBuilder;

        $gatewayId = $factory->create($container, 'aGatewayName', array(
            'actions' => array(),
            'apis' => array(),
            'extensions' => array(
                'payum.extension.foo',
                'payum.extension.bar',
            ),
        ));

        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addExtension',
            new Reference('payum.extension.foo')
        );
        $this->assertDefinitionContainsMethodCall(
            $container->getDefinition($gatewayId),
            'addExtension',
            new Reference('payum.extension.bar')
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractGatewayFactory
     */
    protected function createAbstractGatewayFactory()
    {
        return $this->getMockForAbstractClass('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AbstractGatewayFactory');
    }
}