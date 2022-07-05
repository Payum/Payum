<?php

namespace Payum\Core\Tests\Exception;

use PHPUnit\Framework\TestCase;

class LogicExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\LogicException::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Exception\ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\LogicException::class);

        $this->assertTrue($rc->isSubclassOf(\LogicException::class));
    }
}
