<?php
namespace Payum\Tests\Registry;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Registry\SimpleRegistry;

class SimpleRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass('Payum\Core\Registry\SimpleRegistry');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Registry\AbstractRegistry'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedSetOfArguments()
    {
        new \Payum\Core\Registry\SimpleRegistry(
            $payments = array(),
            $storages = array(),
            $defaultPayment = 'foo',
            $defaultStorage = 'bar'
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentSetInConstructor()
    {
        $paymentFooMock = $this->getMock('Payum\PaymentInterface');
        $paymentBarMock = $this->getMock('Payum\PaymentInterface');

        $registry = new \Payum\Core\Registry\SimpleRegistry(
            $payments = array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(),
            'foo',
            'bar'
        );

        $this->assertSame($paymentFooMock, $registry->getPayment('foo'));
        $this->assertSame($paymentBarMock, $registry->getPayment('bar'));

        //default
        $this->assertSame($paymentFooMock, $registry->getPayment());
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentsSetInConstructor()
    {
        $paymentFooMock = $this->getMock('Payum\PaymentInterface');
        $paymentBarMock = $this->getMock('Payum\PaymentInterface');

        $registry = new SimpleRegistry(
            $payments = array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(),
            'foo',
            'bar'
        );

        $payments = $registry->getPayments();

        $this->assertContains($paymentFooMock, $payments);
        $this->assertContains($paymentBarMock, $payments);
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultPaymentNameSetInConstructor()
    {
        $registry = new SimpleRegistry(
            array(),
            array(),
            'foo',
            'bar'
        );

        $this->assertEquals('foo', $registry->getDefaultPaymentName());
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageForClass()
    {
        $storageFooMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $registry = new SimpleRegistry(
            array(),
            array(
                'foo' => array(
                    'stdClass' => $storageFooMock,
                    'Payum\Examples\Model\TestModel' => $storageBarMock
                ),
            ),
            'bar',
            'foo'
        );

        $this->assertSame($storageFooMock, $registry->getStorageForClass('stdClass', 'foo'));
        $this->assertSame($storageBarMock, $registry->getStorageForClass('Payum\Examples\Model\TestModel', 'foo'));

        //default payment
        $this->assertSame($storageFooMock, $registry->getStorageForClass('stdClass'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorages()
    {
        $storageFooMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $registry = new SimpleRegistry(
            array(),
            array(
                'foo' => array(
                    'stdClass' => $storageFooMock,
                ),
                'bar' => array(
                    'Payum\Examples\Model\TestModel' => $storageBarMock
                )
            ),
            'bar',
            'foo'
        );

        $storages = $registry->getStorages();

        $this->assertContains($storageFooMock, $storages);
        $this->assertNotContains($storageBarMock, $storages);

        $storages = $registry->getStorages('bar');

        $this->assertContains($storageBarMock, $storages);
        $this->assertNotContains($storageFooMock, $storages);
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultStorageName()
    {
        $registry = new \Payum\Core\Registry\SimpleRegistry(
            array(),
            array(),
            'bar',
            'foo'
        );

        $this->assertEquals('foo', $registry->getDefaultStorageName());
    }

    /**
     * @test
     */
    public function shouldAllowRegisterStorageExtensions()
    {
        $storageMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $testCase = $this;

        $paymentMock = $this->getMock('Payum\PaymentInterface');
        $paymentMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf('Payum\Core\Extension\StorageExtension'))
            ->will($this->returnCallback(function(StorageExtension $extension) use ($storageMock, $testCase) {
                $testCase->assertAttributeSame($storageMock, 'storage', $extension);
            }))
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentMock),
            array('foo' => array('stdClass' => $storageMock)),
            'foo',
            'foo'
        );

        $registry->registerStorageExtensions();
    }

    /**
     * @test
     */
    public function shouldRegisterStorageExtensionsForEachStorageInRegistry()
    {
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageTwoMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageThreeMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $paymentFooMock = $this->getMock('Payum\PaymentInterface');
        $paymentFooMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $paymentBarMock = $this->getMock('Payum\PaymentInterface');
        $paymentBarMock
            ->expects($this->exactly(1))
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(
                'foo' => array(
                    'fooClass' => $storageOneMock,
                    'barClass' => $storageTwoMock,
                    'ololClass' => $storageThreeMock,
                ),
                'bar' => array(
                    'fooClass' => $storageOneMock,
                )
            ),
            'foo',
            'foo'
        );

        $registry->registerStorageExtensions();
    }
}