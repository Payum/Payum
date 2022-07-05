<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DoVoidTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(DoVoid::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
