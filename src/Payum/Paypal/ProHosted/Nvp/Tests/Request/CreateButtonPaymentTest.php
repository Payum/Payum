<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;
use PHPUnit\Framework\TestCase;

class CreateButtonPaymentTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreateButtonPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
