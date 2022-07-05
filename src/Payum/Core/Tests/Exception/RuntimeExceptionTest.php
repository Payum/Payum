<?php

namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class RuntimeExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\RuntimeException::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\RuntimeException::class);

        $this->assertTrue($rc->isSubclassOf(RuntimeException::class));
    }
}
