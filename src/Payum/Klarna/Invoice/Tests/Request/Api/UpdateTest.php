<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\Update;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UpdateTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder(): void
    {
        $rc = new ReflectionClass(Update::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
