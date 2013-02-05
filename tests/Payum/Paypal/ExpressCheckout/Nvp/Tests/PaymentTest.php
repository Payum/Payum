<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use Payum\Paypal\ProCheckout\Nvp\Payment;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mustBeConstructed()
    {
        $apiMock = $this->getMock('Payum\Paypal\ProCheckout\Nvp\Api', array(), array(), '', false);
        new Payment($apiMock);
    }
}
