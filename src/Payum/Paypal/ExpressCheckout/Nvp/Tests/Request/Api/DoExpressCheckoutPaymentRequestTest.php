<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

class DoExpressCheckoutPaymentRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelRequest'));
    }
}
