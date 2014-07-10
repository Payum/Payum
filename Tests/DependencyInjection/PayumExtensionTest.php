<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfExtension()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\HttpKernel\DependencyInjection\Extension'));
    }

    /**
     * @test
     */
    public function shouldImplementPrependExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension');

        $this->assertTrue($rc->implementsInterface('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumExtension;
    }

    /**
     * @test
     */
    public function shouldAllowAddPaymentFactory()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addPaymentFactory($factory);
        
        $this->assertAttributeContains($factory, 'paymentFactories', $extension);
    }
    

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The payment factory Mock_PaymentFactoryInterface
     */
    public function throwIfTryToAddPaymentFactoryWithEmptyName()
    {
        $factoryWithEmptyName = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface');
        $factoryWithEmptyName
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(''))
        ;
        
        $extension = new PayumExtension;
        $extension->addPaymentFactory($factoryWithEmptyName);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The payment factory with such name theFoo already registered
     */
    public function throwIfTryToAddPaymentFactoryWithNameAlreadyAdded()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addPaymentFactory($factory);
        $extension->addPaymentFactory($factory);
    }

    /**
     * @test
     */
    public function shouldAllowAddStorageFactory()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');
        $factory
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);

        $this->assertAttributeContains($factory, 'storageFactories', $extension);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The storage factory Mock_StorageFactoryInterface_
     */
    public function throwIfTryToAddStorageFactoryWithEmptyName()
    {
        $factoryWithEmptyName = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');
        $factoryWithEmptyName
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(''))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factoryWithEmptyName);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\InvalidArgumentException
     * @expectedExceptionMessage The storage factory with such name theFoo already registered
     */
    public function throwIfTryToAddStoragePaymentFactoryWithNameAlreadyAdded()
    {
        $factory = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface');
        $factory
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue('theFoo'))
        ;

        $extension = new PayumExtension;
        $extension->addStorageFactory($factory);
        $extension->addStorageFactory($factory);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfPaymentFactoryNotImeplementPreprendFactoryInterface()
    {
        $factoryMock = $this->getMock('Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface');
        $factoryMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('aFactory'))
        ;

        //guard
        $this->assertNotInstanceOf('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface', $factoryMock);

        $extension = new PayumExtension;
        $extension->addPaymentFactory($factoryMock);

        $container = new ContainerBuilder;

        $extension->prepend($container);

        $this->assertEmpty($container->getExtensionConfig('twig'));
    }

    /**
     * @test
     */
    public function shouldPassContainerToPaymentFactoryPrependMethodIfImplementsPrependFactoryInterface()
    {
        $this->markTestSkipped('The logic was disabled because of the bug. See https://github.com/symfony/symfony/pull/9719');

        $container = new ContainerBuilder;

        $factoryMock = $this->getMock('Payum\Bundle\PayumBundle\Tests\DependencyInjection\FactoryPlusPrependExtension');
        $factoryMock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('aFactory'))
        ;
        $factoryMock
            ->expects($this->any())
            ->method('prepend')
            ->with($this->identicalTo($container))
            ->will($this->returnCallback(function(ContainerBuilder $container) {
                $container->prependExtensionConfig('twig', array('foo' => 'fooVal'));
                $container->prependExtensionConfig('twig', array('bar' => 'barVal'));
            }))
        ;

        //guard
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface', $factoryMock);

        $extension = new PayumExtension;
        $extension->addPaymentFactory($factoryMock);


        $extension->prepend($container);

        $this->assertEquals(array(array('bar' => 'barVal'), array('foo' => 'fooVal')), $container->getExtensionConfig('twig'));
    }
}

interface FactoryPlusPrependExtension extends PaymentFactoryInterface, PrependExtensionInterface
{
}