<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use Payum\Paypal\ProCheckout\Nvp\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        $apiMock = $this->getMock('Payum\Paypal\ProCheckout\Nvp\Api', array(), array(), '', false);
        new Payment($apiMock);
    }
}
