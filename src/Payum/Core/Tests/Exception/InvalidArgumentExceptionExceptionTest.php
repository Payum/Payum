<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\InvalidArgumentException');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }

    public function testShouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\InvalidArgumentException');

        $this->assertTrue($rc->isSubclassOf('InvalidArgumentException'));
    }
}
