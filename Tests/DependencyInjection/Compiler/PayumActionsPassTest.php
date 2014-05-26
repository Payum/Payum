<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\PayumActionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class PayumActionsPassTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsCompilerPassInteface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Compiler\PayumActionsPass');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumActionsPass();
    }

    /**
     * @test
     */
    public function shouldAddSingleActionToPaymentsByFactoryName()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $paymentBar = new Definition;
        $paymentBar->addTag('payum.payment', array(
            'factory' => 'bar',
        ));
        $container->setDefinition('payment.bar', $paymentBar);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $actionBar = new Definition;
        $actionBar->addTag('payum.action', array(
            'factory' => 'bar',
        ));
        $container->setDefinition('action.bar', $actionBar);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);
        $this->assertEquals('addAction', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.foo', (string) $fooPaymentMethodCalls[0][1][0]);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
        $this->assertEquals('addAction', $barPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $barPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.bar', (string) $barPaymentMethodCalls[0][1][0]);
    }

    /**
     * @test
     */
    public function shouldAddSeveralActionsToPayment()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $actionFoo1 = new Definition;
        $actionFoo1->addTag('payum.action', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('action.foo1', $actionFoo1);

        $actionFoo2 = new Definition;
        $actionFoo2->addTag('payum.action', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('action.foo2', $actionFoo2);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(2, $fooPaymentMethodCalls);

        $this->assertEquals('addAction', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.foo1', (string) $fooPaymentMethodCalls[0][1][0]);

        $this->assertEquals('addAction', $fooPaymentMethodCalls[1][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[1][1][0]);
        $this->assertEquals('action.foo2', (string) $fooPaymentMethodCalls[1][1][0]);
    }

    /**
     * @test
     */
    public function shouldAddSingleActionWithPrependTrueToPayment()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'factory' => 'foo', 'prepend' => true
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $this->assertEquals('addAction', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.foo', (string) $fooPaymentMethodCalls[0][1][0]);
        $this->assertTrue($fooPaymentMethodCalls[0][1][1]);
    }

    /**
     * @test
     */
    public function shouldAddSingleActionWithPrependFalseToPayment()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'factory' => 'foo', 'prepend' => false
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $this->assertEquals('addAction', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.foo', (string) $fooPaymentMethodCalls[0][1][0]);
        $this->assertFalse($fooPaymentMethodCalls[0][1][1]);
    }

    /**
     * @test
     */
    public function shouldAddSingleActionToPaymentsByContextName()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'context' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $paymentBar = new Definition;
        $paymentBar->addTag('payum.payment', array(
            'context' => 'bar',
        ));
        $container->setDefinition('payment.bar', $paymentBar);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'context' => 'foo',
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $actionBar = new Definition;
        $actionBar->addTag('payum.action', array(
            'context' => 'bar',
        ));
        $container->setDefinition('action.bar', $actionBar);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);
        $this->assertEquals('addAction', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.foo', (string) $fooPaymentMethodCalls[0][1][0]);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
        $this->assertEquals('addAction', $barPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $barPaymentMethodCalls[0][1][0]);
        $this->assertEquals('action.bar', (string) $barPaymentMethodCalls[0][1][0]);
    }

    /**
     * @test
     */
    public function shouldNotAddActionTwiceByFactoryAndContext()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'context' => 'foo_context', 'factory' => 'foo_factory'
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'context' => 'foo_context', 'factory' => 'foo_factory'
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);
    }

    /**
     * @test
     */
    public function shouldAddActionToAllPayments()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array());
        $container->setDefinition('payment.foo', $paymentFoo);

        $paymentBar = new Definition;
        $paymentBar->addTag('payum.payment', array());
        $container->setDefinition('payment.bar', $paymentBar);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'all' => true
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
    }

    /**
     * @test
     */
    public function shouldNotAddActionTwiceIfAllAndFactorySet()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo'
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $paymentBar = new Definition;
        $paymentBar->addTag('payum.payment', array());
        $container->setDefinition('payment.bar', $paymentBar);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'all' => true, 'factory' => 'foo'
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
    }

    /**
     * @test
     */
    public function shouldNotAddActionTwiceIfAllAndContextSet()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'context' => 'foo'
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $paymentBar = new Definition;
        $paymentBar->addTag('payum.payment', array());
        $container->setDefinition('payment.bar', $paymentBar);

        $actionFoo = new Definition;
        $actionFoo->addTag('payum.action', array(
            'all' => true, 'context' => 'foo'
        ));
        $container->setDefinition('action.foo', $actionFoo);

        $pass = new PayumActionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
    }
}
