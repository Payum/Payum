<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\LogicException;
use PHPUnit\Framework\TestCase;

class LogicExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\LogicException');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\LogicException');

        $this->assertTrue($rc->isSubclassOf('LogicException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new LogicException();
    }
}
