<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\BaseGetStatus;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;

class BaseGetStatusTest extends TestCase
{
    public function testShouldImplementGetStatusInterface()
    {
        $rc = new \ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->implementsInterface(GetStatusInterface::class));
    }

    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass(BaseGetStatus::class);

        $this->assertTrue($rc->isAbstract());
    }
}
