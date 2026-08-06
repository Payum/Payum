<?php

declare(strict_types=1);

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReserveAmountTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(ReserveAmount::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
