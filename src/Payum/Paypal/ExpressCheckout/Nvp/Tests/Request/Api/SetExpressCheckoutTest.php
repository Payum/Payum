<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SetExpressCheckoutTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(SetExpressCheckout::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
