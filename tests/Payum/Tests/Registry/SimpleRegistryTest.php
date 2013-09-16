<?php
namespace Payum\Tests\Registry;

use Payum\Registry\SimpleRegistry;

class SimpleRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractRegistry()
    {
        $rc = new \ReflectionClass('Payum\Registry\SimpleRegistry');

        $this->assertTrue($rc->isSubclassOf('Payum\Registry\AbstractRegistry'));
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
        $paymentFooMock = $this->getMock('Payum\PaymentInterface');
        $paymentBarMock = $this->getMock('Payum\PaymentInterface');

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
        $storageFooMock = $this->getMock('Payum\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Storage\StorageInterface');

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
        $storageFooMock = $this->getMock('Payum\Storage\StorageInterface');
        $storageBarMock = $this->getMock('Payum\Storage\StorageInterface');

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
        $registry = new SimpleRegistry(
            array(),
            array(),
            'bar',
            'foo'
        );

        $this->assertEquals('foo', $registry->getDefaultStorageName());
    }
}