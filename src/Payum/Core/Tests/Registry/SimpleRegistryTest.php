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
            $defaultPayment = 'foo'
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentSetInConstructor()
    {
        $paymentFooMock = $this->getMock('Payum\Core\Payment');
        $paymentBarMock = $this->getMock('Payum\Core\Payment');

        $registry = new SimpleRegistry(
            $payments = array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(),
            'foo'
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
        $paymentFooMock = $this->getMock('Payum\Core\Payment');
        $paymentBarMock = $this->getMock('Payum\Core\Payment');

        $registry = new SimpleRegistry(
            $payments = array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(),
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
            'foo'
        );

        $this->assertEquals('foo', $registry->getDefaultPaymentName());
    }

    /**
     * @test
     */
    public function shouldAllowGetStorageForGivenClass()
    {
        $storageFooMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $registry = new SimpleRegistry(
            array(),
            array(
                'stdClass' => $storageFooMock,
                'Payum\Core\Tests\Mocks\Model\TestModel' => $storageBarMock,
            ),
            'bar'
        );

        $this->assertSame($storageFooMock, $registry->getStorage('stdClass'));
        $this->assertSame($storageBarMock, $registry->getStorage('Payum\Core\Tests\Mocks\Model\TestModel'));
    }

    /**
     * @test
     */
    public function shouldAllowGetStorages()
    {
        $storageFooMock = $this->getMock('Payum\Core\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $storages = array(
            'stdClass' => $storageFooMock,
            'Payum\Core\Tests\Mocks\Model\TestModel' => $storageBarMock,
        );

        $registry = new SimpleRegistry(
            array(),
            $storages,
            'bar'
        );

        $this->assertEquals($storages, $registry->getStorages());
    }

    /**
     * @test
     */
    public function shouldInitializeStorageExtensionOnlyOnFirstCallGetPayment()
    {
        $storageMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $testCase = $this;

        $paymentMock = $this->getMock('Payum\Core\Payment');
        $paymentMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf('Payum\Core\Extension\StorageExtension'))
            ->will($this->returnCallback(function (StorageExtension $extension) use ($storageMock, $testCase) {
                $testCase->assertAttributeSame($storageMock, 'storage', $extension);
            }))
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentMock),
            array('stdClass' => $storageMock),
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

        $paymentMock = $this->getMock('Payum\Core\Payment');
        $paymentMock
            ->expects($this->once())
            ->method('addExtension')
            ->with($this->isInstanceOf('Payum\Core\Extension\StorageExtension'))
            ->will($this->returnCallback(function (StorageExtension $extension) use ($storageMock, $testCase) {
                $testCase->assertAttributeSame($storageMock, 'storage', $extension);
            }))
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentMock),
            array('stdClass' => $storageMock),
            'foo'
        );

        $this->assertSame($paymentMock, $registry->getPayment());
    }

    /**
     * @test
     */
    public function shouldNotInitializeStorageExtensionsIfAnyStoragesAssociatedWithPayment()
    {
        $paymentMock = $this->getMock('Payum\Core\Payment');
        $paymentMock
            ->expects($this->never())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentMock),
            array(),
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

        $paymentFooMock = $this->getMock('Payum\Core\Payment');
        $paymentFooMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $paymentBarMock = $this->getMock('Payum\Core\Payment');
        $paymentBarMock
            ->expects($this->exactly(3))
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(
                'fooClass' => $storageOneMock,
                'barClass' => $storageTwoMock,
                'ololClass' => $storageThreeMock,
            ),
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

        $paymentFooMock = $this->getMock('Payum\Core\Payment');
        $paymentFooMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $paymentBarMock = $this->getMock('Payum\Core\Payment');
        $paymentBarMock
            ->expects($this->once())
            ->method('addExtension')
        ;

        $registry = new SimpleRegistry(
            array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(
                'fooClass' => $storageOneMock,
            ),
            'foo'
        );

        $registry->getPayments();
        $registry->getPayments();
    }

    /**
     * @test
     */
    public function shouldNotInitializeStorageExtensionsOnGetPaymentsIfNotGenericPayment()
    {
        $storageOneMock = $this->getMock('Payum\Core\Storage\StorageInterface');

        $paymentFooMock = $this->getMock('Payum\Core\PaymentInterface');

        $paymentBarMock = $this->getMock('Payum\Core\Payment');

        $registry = new SimpleRegistry(
            array('foo' => $paymentFooMock, 'bar' => $paymentBarMock),
            array(
                'fooClass' => $storageOneMock,
            ),
            'foo'
        );

        $registry->getPayments();
    }
}
