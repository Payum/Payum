<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ManageRecurringPaymentsProfileStatusTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(ManageRecurringPaymentsProfileStatus::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
