<?php

declare(strict_types=1);

namespace Payum\Payex\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Payex\Request\Api\CheckRecurringPayment;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CheckRecurringPaymentTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CheckRecurringPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
