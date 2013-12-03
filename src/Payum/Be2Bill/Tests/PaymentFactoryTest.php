<?php
namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\PaymentFactory;

class PaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldNotBeInstantiated()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\PaymentFactory');

        $this->assertFalse($rc->isInstantiable());
    }
    
    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithStandardActionsAdded()
    {
        $apiMock = $this->createApiMock();

        $payment = PaymentFactory::create($apiMock);

        $this->assertInstanceOf('Payum\Core\Payment', $payment);
        
        $this->assertAttributeCount(1, 'apis', $payment);
        
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