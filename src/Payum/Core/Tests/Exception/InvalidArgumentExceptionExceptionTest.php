<?php

namespace Payum\Core\Tests\Exception;

use InvalidArgumentException;
use Payum\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InvalidArgumentExceptionExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\InvalidArgumentException::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\InvalidArgumentException::class);

        $this->assertTrue($rc->isSubclassOf(InvalidArgumentException::class));
    }
}
