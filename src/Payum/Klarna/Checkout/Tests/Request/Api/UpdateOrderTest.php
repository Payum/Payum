<?php

declare(strict_types=1);

namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\BaseOrder;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class UpdateOrderTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(UpdateOrder::class);

        $this->assertTrue($rc->isSubclassOf(BaseOrder::class));
    }
}
