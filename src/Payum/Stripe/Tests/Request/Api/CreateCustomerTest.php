<?php

declare(strict_types=1);

namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\CreateCustomer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CreateCustomerTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CreateCustomer::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
