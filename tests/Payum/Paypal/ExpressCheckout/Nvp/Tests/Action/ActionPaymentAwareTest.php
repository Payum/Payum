<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

class ActionPaymentAwareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function shouldAllowSetPaypapExpressCheckoutPayment()
    {
        $action = $this->getMockForAbstractClass('Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware');

        $action->setPayment(
            $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Payment', array(), array(), '', false)
        );
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid payment given. It must be instance of Payum\Paypal\ExpressCheckout\Nvp\Payment but it is given
     */
    public function throwIfNotPaypapExpressCheckoutPayment()
    {
        $action = $this->getMockForAbstractClass('Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware');

        $action->setPayment($this->getMock('Payum\PaymentInterface'));
    }
}