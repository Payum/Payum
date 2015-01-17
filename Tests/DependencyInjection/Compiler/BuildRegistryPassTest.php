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
        $container->setParameter('payum.available_payment_factories', []);
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
    public function shouldPassPayumTaggedPaymentsAsFirstArgument()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_payment_factories', []);
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.payment.foo', new Definition());
        $container->getDefinition('payum.payment.foo')->addTag('payum.payment', array('payment' => 'fooVal'));
        $container->getDefinition('payum.payment.foo')->addTag('payum.payment', array('payment' => 'barVal'));

        $container->setDefinition('payum.payment.baz', new Definition());
        $container->getDefinition('payum.payment.baz')->addTag('payum.payment', array('payment' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertEquals(array(
            'fooVal' => 'payum.payment.foo',
            'barVal' => 'payum.payment.foo',
            'bazVal' => 'payum.payment.baz',
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
        $container->setParameter('payum.available_payment_factories', []);
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
    public function shouldPassPayumTaggedPaymentFactoriesAsThirdArgument()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_payment_factories', []);
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.payment_factory.foo', new Definition());
        $container->getDefinition('payum.payment_factory.foo')->addTag('payum.payment_factory', array('name' => 'fooVal'));
        $container->getDefinition('payum.payment_factory.foo')->addTag('payum.payment_factory', array('name' => 'barVal'));

        $container->setDefinition('payum.payment_factory.baz', new Definition());
        $container->getDefinition('payum.payment_factory.baz')->addTag('payum.payment_factory', array('name' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertEquals(array(), $registry->getArgument(0));
        $this->assertEquals(array(), $registry->getArgument(1));
        $this->assertEquals(array(
            'fooVal' => 'payum.payment_factory.foo',
            'barVal' => 'payum.payment_factory.foo',
            'bazVal' => 'payum.payment_factory.baz',
        ), $registry->getArgument(2));
    }

    /**
     * @test
     */
    public function shouldPassPaymentsAndStoragesAndPaymentsFactoriesSameTime()
    {
        $registry = new Definition('Payum\Bundle\PayumBundle\Regitry\ContainerAwareRegistry', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setParameter('payum.available_payment_factories', []);
        $container->setDefinition('payum.static_registry', $registry);

        $container->setDefinition('payum.storage.foo', new Definition());
        $container->getDefinition('payum.storage.foo')->addTag('payum.storage', array('model_class' => 'fooVal'));

        $container->setDefinition('payum.payment.baz', new Definition());
        $container->getDefinition('payum.payment.baz')->addTag('payum.payment', array('payment' => 'bazVal'));

        $container->setDefinition('payum.payment_factory.baz', new Definition());
        $container->getDefinition('payum.payment_factory.baz')->addTag('payum.payment_factory', array('name' => 'bazVal'));


        $pass = new BuildRegistryPass;

        $pass->process($container);

        $this->assertNotEmpty($registry->getArgument(0));
        $this->assertNotEmpty($registry->getArgument(1));
        $this->assertNotEmpty($registry->getArgument(2));
    }
}
