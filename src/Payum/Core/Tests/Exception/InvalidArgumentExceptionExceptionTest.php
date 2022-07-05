<?php

namespace Payum\Core\Tests\Exception;

use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\InvalidArgumentException::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Exception\ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\InvalidArgumentException::class);

        $this->assertTrue($rc->isSubclassOf(\InvalidArgumentException::class));
    }
}
