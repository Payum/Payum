<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UpdateRecurringPaymentProfileTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(UpdateRecurringPaymentProfile::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
