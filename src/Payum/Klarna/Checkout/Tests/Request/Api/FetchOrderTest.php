<?php

namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\BaseOrder;
use Payum\Klarna\Checkout\Request\Api\FetchOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FetchOrderTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(FetchOrder::class);

        $this->assertTrue($rc->isSubclassOf(BaseOrder::class));
    }
}
