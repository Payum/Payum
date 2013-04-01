<?php
namespace Payum\Tests\Action;

class ActionPaymentAwareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Action\ActionPaymentAware');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementPaymentAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Action\ActionPaymentAware');

        $this->assertTrue($rc->implementsInterface('Payum\PaymentAwareInterface'));
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