<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use MyProject\Proxies\__CG__\stdClass;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;

class GetTransactionDetailsRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAndPaymentRequestNAsArguments()
    {
        new GetTransactionDetailsRequest(new \stdClass, $paymentRequestN = 5);
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