<?php
namespace Payum\OmnipayBridge\Tests\Action;


class ActionPaymentAwareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\ActionPaymentAware');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function shouldAllowSetPaypapExpressCheckoutPayment()
    {
        $action = $this->getMockForAbstractClass('Payum\OmnipayBridge\Action\ActionPaymentAware');

        $action->setPayment(
            $this->getMock('Payum\OmnipayBridge\Payment', array(), array(), '', false)
        );
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid payment given. It must be instance of Payum\OmnipayBridge\Payment but it is given
     */
    public function throwIfNotPaypapExpressCheckoutPayment()
    {
        $action = $this->getMockForAbstractClass('Payum\OmnipayBridge\Action\ActionPaymentAware');

        $action->setPayment($this->getMock('Payum\PaymentInterface'));
    }
}