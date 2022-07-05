<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

class CheckOrderStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Request\Api\CheckOrderStatus::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Request\Generic::class));
    }
}
