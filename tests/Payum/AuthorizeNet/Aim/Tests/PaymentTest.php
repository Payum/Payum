<?php
namespace Payum\AuthorizeNet\Aim\Tests;

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPayumPayment()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Payment');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Payment'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithApiAsFirstArgument()
    {
        $apiMock = $this->createAuthorizeNetAIMMock();
        
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
        $apiMock = $this->createAuthorizeNetAIMMock();

        $payment = Payment::create($apiMock);

        $this->assertAttributeSame($apiMock, 'api', $payment);
        
        $actions = $this->readAttribute($payment, 'actions');
        $this->assertInternalType('array', $actions);
        $this->assertNotEmpty($actions);
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthorizeNetAIM
     */
    protected function createAuthorizeNetAIMMock()
    {
        return $this->getMock('Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM', array(), array(), '', false);
    }
}