<?php

namespace Payum\Core\Tests\Exception;

use LogicException;
use Payum\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LogicExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\LogicException::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\LogicException::class);

        $this->assertTrue($rc->isSubclassOf(LogicException::class));
    }
}
