<?php
namespace Payum\Bundle\PayumBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\Reference;

class PayumExtensionTest extends  \PHPUnit_Framework_TestCase
{
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
     * @expectedException \Payum\Exception\InvalidArgumentException
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
     * @expectedException \Payum\Exception\InvalidArgumentException
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
     * @expectedException \Payum\Exception\InvalidArgumentException
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
     * @expectedException \Payum\Exception\InvalidArgumentException
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
}