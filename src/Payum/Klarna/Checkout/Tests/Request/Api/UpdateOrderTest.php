<?php

namespace Payum\Klarna\Checkout\Tests\Request\Api;

use PHPUnit\Framework\TestCase;

class UpdateOrderTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Checkout\Request\Api\UpdateOrder::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Klarna\Checkout\Request\Api\BaseOrder::class));
    }
}
