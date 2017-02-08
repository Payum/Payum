<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsTest extends \PHPUnit_Framework_TestCase
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
