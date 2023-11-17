<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\BaseGetStatus;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BaseGetStatusTest extends TestCase
{
    public function testShouldImplementGetStatusInterface(): void
    {
        $rc = new ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->implementsInterface(GetStatusInterface::class));
    }

    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldBeAbstract(): void
    {
        $rc = new ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->isAbstract());
    }
}
