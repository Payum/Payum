<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(GetTransactionDetails::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentRequestNSetInConstructor()
    {
        $expectedPaymentRequestN = 7;

        $request = new GetTransactionDetails(new \stdClass(), $expectedPaymentRequestN);

        $this->assertSame($expectedPaymentRequestN, $request->getPaymentRequestN());
    }
}
