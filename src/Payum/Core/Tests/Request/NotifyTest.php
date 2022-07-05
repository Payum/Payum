<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Notify;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class NotifyTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(Notify::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
