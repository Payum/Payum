<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetExpressCheckoutDetailsTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(GetExpressCheckoutDetails::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
