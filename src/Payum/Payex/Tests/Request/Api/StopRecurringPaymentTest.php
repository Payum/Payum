<?php

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\StopRecurringPayment;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class StopRecurringPaymentTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(StopRecurringPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
