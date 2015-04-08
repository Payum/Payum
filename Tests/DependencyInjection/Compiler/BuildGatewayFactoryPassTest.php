<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildGatewayFactoryPassTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsCompilerPassInteface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildGatewayFactoryPass');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new BuildGatewayFactoryPass();
    }

    /**
     * @test
     */
    public function shouldPassEmptyArraysIfNoTagsDefined()
    {
        $gatewayFactory = new Definition('Payum\Bundle\PayumBundle\GatewayFactory', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setDefinition('payum.gateway_factory', $gatewayFactory);

        $pass = new BuildGatewayFactoryPass;

        $pass->process($container);

        $this->assertEquals(array(), $gatewayFactory->getArgument(0));
        $this->assertEquals(array(), $gatewayFactory->getArgument(1));
        $this->assertEquals(array(), $gatewayFactory->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPayumActionTagsAsFirstArgument()
    {
        $gatewayFactory = new Definition('Payum\Bundle\PayumBundle\GatewayFactory', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setDefinition('payum.gateway_factory', $gatewayFactory);

        $container->setDefinition('payum.action.foo', new Definition());
        $container->getDefinition('payum.action.foo')->addTag('payum.action', array('foo' => 'fooVal'));
        $container->getDefinition('payum.action.foo')->addTag('payum.action', array('bar' => 'barVal'));

        $container->setDefinition('payum.action.baz', new Definition());
        $container->getDefinition('payum.action.baz')->addTag('payum.action', array('baz' => 'bazVal'));


        $pass = new BuildGatewayFactoryPass;

        $pass->process($container);

        $this->assertEquals(array(
            'payum.action.foo' => array(
                array('foo' => 'fooVal'),
                array('bar' => 'barVal'),
            ),
            'payum.action.baz' => array(
                array('baz' => 'bazVal')
            ),
        ), $gatewayFactory->getArgument(0));
        $this->assertEquals(array(), $gatewayFactory->getArgument(1));
        $this->assertEquals(array(), $gatewayFactory->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPayumExtensionTagsAsSecondArgument()
    {
        $gatewayFactory = new Definition('Payum\Bundle\PayumBundle\GatewayFactory', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setDefinition('payum.gateway_factory', $gatewayFactory);

        $container->setDefinition('payum.extension.foo', new Definition());
        $container->getDefinition('payum.extension.foo')->addTag('payum.extension', array('foo' => 'fooVal'));
        $container->getDefinition('payum.extension.foo')->addTag('payum.extension', array('bar' => 'barVal'));

        $container->setDefinition('payum.extension.baz', new Definition());
        $container->getDefinition('payum.extension.baz')->addTag('payum.extension', array('baz' => 'bazVal'));


        $pass = new BuildGatewayFactoryPass;

        $pass->process($container);

        $this->assertEquals(array(), $gatewayFactory->getArgument(0));
        $this->assertEquals(array(
            'payum.extension.foo' => array(
                array('foo' => 'fooVal'),
                array('bar' => 'barVal'),
            ),
            'payum.extension.baz' => array(
                array('baz' => 'bazVal')
            ),
        ), $gatewayFactory->getArgument(1));
        $this->assertEquals(array(), $gatewayFactory->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPayumApiTagsAsThirdArgument()
    {
        $gatewayFactory = new Definition('Payum\Bundle\PayumBundle\GatewayFactory', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setDefinition('payum.gateway_factory', $gatewayFactory);

        $container->setDefinition('payum.api.foo', new Definition());
        $container->getDefinition('payum.api.foo')->addTag('payum.api', array('foo' => 'fooVal'));
        $container->getDefinition('payum.api.foo')->addTag('payum.api', array('bar' => 'barVal'));

        $container->setDefinition('payum.api.baz', new Definition());
        $container->getDefinition('payum.api.baz')->addTag('payum.api', array('baz' => 'bazVal'));


        $pass = new BuildGatewayFactoryPass;

        $pass->process($container);

        $this->assertEquals(array(), $gatewayFactory->getArgument(0));
        $this->assertEquals(array(), $gatewayFactory->getArgument(1));
        $this->assertEquals(array(
            'payum.api.foo' => array(
                array('foo' => 'fooVal'),
                array('bar' => 'barVal'),
            ),
            'payum.api.baz' => array(
                array('baz' => 'bazVal')
            ),
        ), $gatewayFactory->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassActionExtensionApiTagsAtOnce()
    {
        $gatewayFactory = new Definition('Payum\Bundle\PayumBundle\GatewayFactory', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setDefinition('payum.gateway_factory', $gatewayFactory);

        $container->setDefinition('payum.api.foo', new Definition());
        $container->getDefinition('payum.api.foo')->addTag('payum.api', array('foo' => 'fooVal'));

        $container->setDefinition('payum.extension.bar', new Definition());
        $container->getDefinition('payum.extension.bar')->addTag('payum.extension', array('bar' => 'barVal'));

        $container->setDefinition('payum.action.baz', new Definition());
        $container->getDefinition('payum.action.baz')->addTag('payum.action', array('baz' => 'bazVal'));


        $pass = new BuildGatewayFactoryPass;

        $pass->process($container);

        $this->assertNotEmpty($gatewayFactory->getArgument(0));
        $this->assertNotEmpty($gatewayFactory->getArgument(1));
        $this->assertNotEmpty($gatewayFactory->getArgument(2));
    }
}
