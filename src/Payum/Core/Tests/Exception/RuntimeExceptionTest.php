<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\RuntimeException;

class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\RuntimeException');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\RuntimeException');

        $this->assertTrue($rc->isSubclassOf('RuntimeException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RuntimeException();
    }
}
