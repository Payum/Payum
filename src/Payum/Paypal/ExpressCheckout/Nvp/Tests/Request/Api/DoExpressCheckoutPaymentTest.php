<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;

class DoExpressCheckoutPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment');

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
