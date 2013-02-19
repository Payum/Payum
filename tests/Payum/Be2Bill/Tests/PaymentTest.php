<?php
namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\Payment;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Be2Bill\Action\CaptureAction;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPayumPayment()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Payment');
        
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
        return $this->getMock('Payum\Be2bill\Api', array(), array(), '', false);
    }
}