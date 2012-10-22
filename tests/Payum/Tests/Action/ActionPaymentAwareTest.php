<?php
namespace Payum\Tests\Action;

class ActionPaymentAwareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionPaymentAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Action\ActionPaymentAware');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionPaymentAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldSetPaymentToProperty()
    {
        $payment = $this->getMock('Payum\PaymentInterface');
        
        $action = $this->getMockForAbstractClass('Payum\Action\ActionPaymentAware');
        
        $action->setPayment($payment);
        
        $this->assertAttributeSame($payment, 'payment', $action);
    }
}