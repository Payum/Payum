<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\InvalidArgumentException');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\InvalidArgumentException');

        $this->assertTrue($rc->isSubclassOf('InvalidArgumentException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new InvalidArgumentException();
    }
}
