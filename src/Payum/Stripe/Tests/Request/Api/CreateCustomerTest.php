<?php

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreateCustomer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreateCustomerTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(CreateCustomer::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
