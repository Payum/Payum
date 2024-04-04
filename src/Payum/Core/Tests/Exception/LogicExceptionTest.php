<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\LogicException;
use PHPUnit\Framework\TestCase;

class LogicExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\LogicException');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\LogicException');

        $this->assertTrue($rc->isSubclassOf('LogicException'));
    }
}
