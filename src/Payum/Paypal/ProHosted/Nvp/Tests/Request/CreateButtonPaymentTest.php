<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;

class CreateButtonPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreateButtonPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
