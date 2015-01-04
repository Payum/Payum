<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildPaymentFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuildPaymentFactoryPassTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsCompilerPassInteface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Compiler\BuildPaymentFactoryPass');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new BuildPaymentFactoryPass();
    }

    /**
     * @test
     */
    public function shouldPassEmptyArraysIfNoTagsDefined()
    {
        $paymentFactory = new Definition('Payum\Bundle\PayumBundle\PaymentFactory', array(null, null, null));

        $container = new ContainerBuilder;
        $container->setDefinition('payum.payment_factory', $paymentFactory);

        $pass = new BuildPaymentFactoryPass;

        $pass->process($container);

        $this->assertEquals(array(), $paymentFactory->getArgument(0));
        $this->assertEquals(array(), $paymentFactory->getArgument(1));
        $this->assertEquals(array(), $paymentFactory->getArgument(2));
    }
}
