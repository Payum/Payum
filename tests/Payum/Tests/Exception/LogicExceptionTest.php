<?php
namespace Payum\Tests\Exception;

use Payum\Exception\LogicException;

class LogicExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Exception\LogicException');
        
        $this->assertTrue($rc->implementsInterface('Payum\Exception\ExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Exception\LogicException');

        $this->assertTrue($rc->isSubclassOf('LogicException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new LogicException;
    }
}
