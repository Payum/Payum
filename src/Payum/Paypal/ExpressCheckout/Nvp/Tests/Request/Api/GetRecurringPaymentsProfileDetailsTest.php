<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetRecurringPaymentsProfileDetailsTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(GetRecurringPaymentsProfileDetails::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
