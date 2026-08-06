<?php

declare(strict_types=1);

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\ReturnAmount;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReturnAmountTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(ReturnAmount::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
