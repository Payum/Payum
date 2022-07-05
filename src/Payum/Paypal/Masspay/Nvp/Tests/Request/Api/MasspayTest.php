<?php

namespace Payum\Paypal\Masspay\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MasspayTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(Masspay::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
