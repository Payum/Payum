<?php
namespace Payum\Core\Tests\Registry;

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
        new SimpleRegistry(
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
        $paymentFooMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentBarMock = $this->getMock('Payum\Core\PaymentInterface');

        $registry = new SimpleRegistry(
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
        $paymentFooMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentBarMock = $this->getMock('Payum\Core\PaymentInterface');

        $registry = new SimpleRegistry(
            $payments = array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(),
            'foo',
            'foo'
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
                    'Payum\Core\Tests\Mocks\Model\TestModel' => $storageBarMock
                ),
            ),
            'bar',
            'foo'
        );

        $this->assertSame($storageFooMock, $registry->getStorageForClass('stdClass', 'foo'));
        $this->assertSame($storageBarMock, $registry->getStorageForClass('Payum\Core\Tests\Mocks\Model\TestModel', 'foo'));

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
                    'Payum\Core\Tests\Mocks\Model\TestModel' => $storageBarMock
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
        $registry = new SimpleRegistry(
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
    public function shouldInitializeStorageExtensionOnlyOnFirstCallGetPayment()
    {
        $storageMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $testCase = $this;

        $paymentMock = $this->getMock('Payum\Core\PaymentInterface');
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

        $this->assertSame($paymentMock, $registry->getPayment('foo'));
        $this->assertSame($paymentMock, $registry->getPayment('foo'));
        $this->assertSame($paymentMock, $registry->getPayment('foo'));
    }

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionForDefaultPayment()
    {
        $storageMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $testCase = $this;

        $paymentMock = $this->getMock('Payum\Core\PaymentInterface');
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

        $this->assertSame($paymentMock, $registry->getPayment());
    }

    /**
     * @test
     */
    public function shouldNotInitializeStorageExtensionsIfAnyStoragesAssociatedWithPayment()
    {
        $paymentMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentMock
            ->expects($this->never())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentMock),
            array(),
            'foo',
            'foo'
        );

        $registry->getPayment('foo');
    }

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionsForEachStorageInRegistry()
    {
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageTwoMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageThreeMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $paymentFooMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentFooMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $paymentBarMock = $this->getMock('Payum\Core\PaymentInterface');
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

        $registry->getPayment('foo');
        $registry->getPayment('bar');
    }

        /**
     * @test
     */
    public function shouldInitializeStorageExtensionsOnGetPayments()
    {
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $paymentFooMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentFooMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $paymentBarMock = $this->getMock('Payum\Core\PaymentInterface');
        $paymentBarMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(
                'foo' => array('fooClass' => $storageOneMock),
                'bar' => array('fooClass' => $storageOneMock)
            ),
            'foo',
            'foo'
        );

        $registry->getPayments();
        $registry->getPayments();
    }
}
