<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\StartRecurringPayment;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StartRecurringPaymentTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(StartRecurringPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
