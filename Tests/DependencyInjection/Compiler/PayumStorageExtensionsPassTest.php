<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection\Compiler;

use Payum\Bundle\PayumBundle\DependencyInjection\Compiler\PayumStorageExtensionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class PayumStorageExtensionsPassTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsCompilerPassInteface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\Compiler\PayumStorageExtensionsPass');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumStorageExtensionsPass();
    }

    /**
     * @test
     */
    public function shouldAddSingleStorageToPaymentsByFactoryName()
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

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $storageBar = new Definition;
        $storageBar->addTag('payum.storage_extension', array(
            'factory' => 'bar',
        ));
        $container->setDefinition('payum.storage.bar', $storageBar);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);
        $this->assertEquals('addExtension', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.foo', (string) $fooPaymentMethodCalls[0][1][0]);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
        $this->assertEquals('addExtension', $barPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $barPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.bar', (string) $barPaymentMethodCalls[0][1][0]);
    }

    /**
     * @test
     */
    public function shouldAddSeveralstoragesToPayment()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $storageFoo1 = new Definition;
        $storageFoo1->addTag('payum.storage_extension', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payum.storage.foo1', $storageFoo1);

        $storageFoo2 = new Definition;
        $storageFoo2->addTag('payum.storage_extension', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payum.storage.foo2', $storageFoo2);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(2, $fooPaymentMethodCalls);

        $this->assertEquals('addExtension', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.foo1', (string) $fooPaymentMethodCalls[0][1][0]);

        $this->assertEquals('addExtension', $fooPaymentMethodCalls[1][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[1][1][0]);
        $this->assertEquals('payum.extension.storage.foo2', (string) $fooPaymentMethodCalls[1][1][0]);
    }

    /**
     * @test
     */
    public function shouldAddSingleStorageWithPrependTrueToPayment()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'factory' => 'foo', 'prepend' => true
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $this->assertEquals('addExtension', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.foo', (string) $fooPaymentMethodCalls[0][1][0]);
        $this->assertTrue($fooPaymentMethodCalls[0][1][1]);
    }

    /**
     * @test
     */
    public function shouldAddSingleStorageWithPrependFalseToPayment()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'factory' => 'foo', 'prepend' => false
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $this->assertEquals('addExtension', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.foo', (string) $fooPaymentMethodCalls[0][1][0]);
        $this->assertFalse($fooPaymentMethodCalls[0][1][1]);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage In order to add storage to extension the storage d has to contains ".storage." inside.
     */
    public function throwIfStorageServiceIdDoesNotMatchRequiredPattern()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'factory' => 'foo',
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'factory' => 'foo', 'prepend' => false
        ));
        $container->setDefinition('foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);
    }

    /**
     * @test
     */
    public function shouldAddSinglestorageToPaymentsByContextName()
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

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'context' => 'foo',
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $storageBar = new Definition;
        $storageBar->addTag('payum.storage_extension', array(
            'context' => 'bar',
        ));
        $container->setDefinition('payum.storage.bar', $storageBar);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);
        $this->assertEquals('addExtension', $fooPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $fooPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.foo', (string) $fooPaymentMethodCalls[0][1][0]);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
        $this->assertEquals('addExtension', $barPaymentMethodCalls[0][0]);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $barPaymentMethodCalls[0][1][0]);
        $this->assertEquals('payum.extension.storage.bar', (string) $barPaymentMethodCalls[0][1][0]);
    }

    /**
     * @test
     */
    public function shouldNotaddExtensionTwiceByFactoryAndContext()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array(
            'context' => 'foo_context', 'factory' => 'foo_factory'
        ));
        $container->setDefinition('payment.foo', $paymentFoo);

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'context' => 'foo_context', 'factory' => 'foo_factory'
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);
    }

    /**
     * @test
     */
    public function shouldaddExtensionToAllPayments()
    {
        $container = new ContainerBuilder;

        $paymentFoo = new Definition;
        $paymentFoo->addTag('payum.payment', array());
        $container->setDefinition('payment.foo', $paymentFoo);

        $paymentBar = new Definition;
        $paymentBar->addTag('payum.payment', array());
        $container->setDefinition('payment.bar', $paymentBar);

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'all' => true
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
    }

    /**
     * @test
     */
    public function shouldNotaddExtensionTwiceIfAllAndFactorySet()
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

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'all' => true, 'factory' => 'foo'
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
    }

    /**
     * @test
     */
    public function shouldNotaddExtensionTwiceIfAllAndContextSet()
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

        $storageFoo = new Definition;
        $storageFoo->addTag('payum.storage_extension', array(
            'all' => true, 'context' => 'foo'
        ));
        $container->setDefinition('payum.storage.foo', $storageFoo);

        $pass = new PayumStorageExtensionsPass;

        $pass->process($container);

        $fooPaymentMethodCalls = $paymentFoo->getMethodCalls();
        $this->assertCount(1, $fooPaymentMethodCalls);

        $barPaymentMethodCalls = $paymentBar->getMethodCalls();
        $this->assertCount(1, $barPaymentMethodCalls);
    }
}
