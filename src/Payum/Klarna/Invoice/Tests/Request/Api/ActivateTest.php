<?php

declare(strict_types=1);

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\Activate;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ActivateTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(Activate::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
