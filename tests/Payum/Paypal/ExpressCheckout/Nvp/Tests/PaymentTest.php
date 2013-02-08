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
        new Payment($this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false));
    }
}
