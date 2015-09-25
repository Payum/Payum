<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildRegistryPassTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsCompilerPassInteface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildRegistryPass');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new BuildRegistryPass();
    }

    /**
     * @test
     */
    public function shouldPassEmptyArraysIfNoTagsDefined()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_gateway_factories', array());
        $container->setDefinition('payum.static_registry', $registry);

        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertEquals(array(), $registry->getArgument(0));
        $this->assertEquals(array(), $registry->getArgument(1));
        $this->assertEquals(array(), $registry->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPayumTaggedGatewaysAsFirstArgument()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_gateway_factories', array());
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.gateway.foo', new Definition());
        $container->getDefinition('payum.gateway.foo')->addTag('payum.gateway', array('gateway' => 'fooVal'));
        $container->getDefinition('payum.gateway.foo')->addTag('payum.gateway', array('gateway' => 'barVal'));

        $container->setDefinition('payum.gateway.baz', new Definition());
        $container->getDefinition('payum.gateway.baz')->addTag('payum.gateway', array('gateway' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertEquals(array(
            'fooVal' => 'payum.gateway.foo',
            'barVal' => 'payum.gateway.foo',
            'bazVal' => 'payum.gateway.baz',
        ), $registry->getArgument(0));
        $this->assertEquals(array(), $registry->getArgument(1));
        $this->assertEquals(array(), $registry->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPayumTaggedStoragesAsSecondArgument()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_gateway_factories', array());
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.storage.foo', new Definition());
        $container->getDefinition('payum.storage.foo')->addTag('payum.storage', array('model_class' => 'fooVal'));
        $container->getDefinition('payum.storage.foo')->addTag('payum.storage', array('model_class' => 'barVal'));

        $container->setDefinition('payum.storage.baz', new Definition());
        $container->getDefinition('payum.storage.baz')->addTag('payum.storage', array('model_class' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertEquals(array(), $registry->getArgument(0));
        $this->assertEquals(array(
            'fooVal' => 'payum.storage.foo',
            'barVal' => 'payum.storage.foo',
            'bazVal' => 'payum.storage.baz',
        ), $registry->getArgument(1));
        $this->assertEquals(array(), $registry->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPayumTaggedGatewayFactoriesAsThirdArgument()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_gateway_factories', array());
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.gateway_factory.foo', new Definition());
        $container->getDefinition('payum.gateway_factory.foo')->addTag('payum.gateway_factory', array('factory_name' => 'fooVal'));
        $container->getDefinition('payum.gateway_factory.foo')->addTag('payum.gateway_factory', array('factory_name' => 'barVal'));

        $container->setDefinition('payum.gateway_factory.baz', new Definition());
        $container->getDefinition('payum.gateway_factory.baz')->addTag('payum.gateway_factory', array('factory_name' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertEquals(array(), $registry->getArgument(0));
        $this->assertEquals(array(), $registry->getArgument(1));
        $this->assertEquals(array(
            'fooVal' => 'payum.gateway_factory.foo',
            'barVal' => 'payum.gateway_factory.foo',
            'bazVal' => 'payum.gateway_factory.baz',
        ), $registry->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassGatewaysAndStoragesAndGatewaysFactoriesSameTime()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_gateway_factories', array());
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.storage.foo', new Definition());
        $container->getDefinition('payum.storage.foo')->addTag('payum.storage', array('model_class' => 'fooVal'));

        $container->setDefinition('payum.gateway.baz', new Definition());
        $container->getDefinition('payum.gateway.baz')->addTag('payum.gateway', array('gateway' => 'bazVal'));

        $container->setDefinition('payum.gateway_factory.baz', new Definition());
        $container->getDefinition('payum.gateway_factory.baz')->addTag('payum.gateway_factory', array('factory_name' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertNotEmpty($registry->getArgument(0));
        $this->assertNotEmpty($registry->getArgument(1));
        $this->assertNotEmpty($registry->getArgument(2));
    }
}
