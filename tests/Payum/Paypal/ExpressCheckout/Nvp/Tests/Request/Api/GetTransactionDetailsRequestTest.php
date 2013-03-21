<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use MyProject\Proxies\__CG__\stdClass;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest;

class GetTransactionDetailsRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAndPaymentRequestNAsArguments()
    {
        new \Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest(new \stdClass, $paymentRequestN = 5);
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentRequestNSetInConstructor()
    {
        $expectedPaymentRequestN = 7;

        $request = new GetTransactionDetailsRequest(new \stdClass, $expectedPaymentRequestN);

        $this->assertSame($expectedPaymentRequestN, $request->getPaymentRequestN());
    }
}