<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class RuntimeExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\RuntimeException::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Exception\ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\RuntimeException::class);

        $this->assertTrue($rc->isSubclassOf('RuntimeException'));
    }
}
