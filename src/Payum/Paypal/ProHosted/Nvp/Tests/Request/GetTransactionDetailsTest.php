<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;
use PHPUnit\Framework\TestCase;

class GetTransactionDetailsTest extends TestCase
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
    public function couldBeConstructedWithModelAndPaymentRequestNAsArguments()
    {
        new GetTransactionDetails(new \stdClass());
    }
}
