<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests;

use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubclassOfPayumPayment()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Payment');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Payment'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithApiAsFirstArgument()
    {
        $apiMock = $this->createApiMock();
        
        $payment = new Payment($apiMock);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertEmpty($actions);

        $this->assertAttributeSame($apiMock, 'api', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $apiMock = $this->createApiMock();

        $payment = Payment::create($apiMock);

        $this->assertAttributeSame($apiMock, 'api', $payment);

        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}