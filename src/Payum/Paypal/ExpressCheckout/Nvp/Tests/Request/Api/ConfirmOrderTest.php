<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ConfirmOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(ConfirmOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
