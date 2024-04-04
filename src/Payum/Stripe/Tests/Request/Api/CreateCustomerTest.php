<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreateCustomer;

class CreateCustomerTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(CreateCustomer::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
