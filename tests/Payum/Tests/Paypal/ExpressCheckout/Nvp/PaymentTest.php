<?php
namespace Payum\Tests\Paypal\ExpressCheckout\Nvp;

use Payum\Paypal\ExpressCheckout\Nvp\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubclassOfPayumPaymeny()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Payment');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Payment'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Payment;
    }
}
