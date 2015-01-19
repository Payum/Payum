<?php
namespace Payum\Core\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Model\PaymentConfig;
use Payum\Core\Payment;
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\StorageInterface;

class DynamicRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsRegistryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\DynamicRegistry');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Registry\RegistryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithPaymentConfigAndStaticStorageAsArguments()
    {
        new DynamicRegistry($this->createStorageMock(), $this->createRegistryMock());
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryOnGetPayments()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getPayments')
            ->willReturn('thePayments')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );
        
        $this->assertEquals('thePayments', $registry->getPayments());
    }

    /**
     * @test
     */
    public function shouldCreatePaymentUsingConfigOnGetPayment()
    {
        $paymentConfig = new PaymentConfig();
        $paymentConfig->setConfig($config = array('foo' => 'fooVal', 'bar' => 'barVal'));
        $paymentConfig->setFactoryName($factoryName = 'theFactoryName');
        $paymentConfig->setPaymentName($paymentName = 'thePaymentName');

        $payment = new Payment();

        $paymentFactoryMock = $this->getMock('Payum\Core\PaymentFactoryInterface');
        $paymentFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($config)
            ->willReturn($payment)
        ;

        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getPaymentFactory')
            ->with($factoryName)
            ->willReturn($paymentFactoryMock)
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with(array('paymentName' => $paymentName))
            ->willReturn($paymentConfig)
        ;

        $registry = new DynamicRegistry($storageMock, $staticRegistryMock);

        $this->assertSame($payment, $registry->getPayment($paymentName));
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryIfPaymentConfigNotFoundOnGetPayment()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getPayment')
            ->with('thePaymentName')
            ->willReturn('thePayment')
        ;
        $staticRegistryMock
            ->expects($this->never())
            ->method('getPaymentFactory')
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with(array('paymentName' => 'thePaymentName'))
            ->willReturn(null)
        ;

        $registry = new DynamicRegistry($storageMock, $staticRegistryMock);

        $this->assertSame('thePayment', $registry->getPayment('thePaymentName'));
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryOnGetPaymentFactories()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getPaymentFactories')
            ->willReturn('thePaymentsFactories')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('thePaymentsFactories', $registry->getPaymentFactories());
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryOnGetPaymentFactory()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getPaymentFactory')
            ->with('theName')
            ->willReturn('thePaymentFactory')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('thePaymentFactory', $registry->getPaymentFactory('theName'));
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryOnGetStorages()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getStorages')
            ->willReturn('theStorages')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('theStorages', $registry->getStorages());
    }

    /**
     * @test
     */
    public function shouldCallStaticRegistryOnGetStorage()
    {
        $staticRegistryMock = $this->createRegistryMock();
        $staticRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with('theName')
            ->willReturn('theStorage')
        ;

        $registry = new DynamicRegistry(
            $this->createStorageMock(),
            $staticRegistryMock
        );

        $this->assertEquals('theStorage', $registry->getStorage('theName'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RegistryInterface
     */
    protected function createRegistryMock()
    {
        return $this->getMock('Payum\Core\Registry\RegistryInterface');
    }
}
