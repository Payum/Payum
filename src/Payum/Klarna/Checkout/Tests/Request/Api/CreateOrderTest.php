<?php

declare(strict_types=1);

namespace Payum\Klarna\Checkout\Tests\Request\Api;

use Payum\Klarna\Checkout\Request\Api\BaseOrder;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CreateOrderTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(CreateOrder::class);

        $this->assertTrue($rc->isSubclassOf(BaseOrder::class));
    }
}
