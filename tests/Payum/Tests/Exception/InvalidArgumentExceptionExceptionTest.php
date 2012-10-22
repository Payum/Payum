<?php
namespace Payum\Tests\Exception;

use Payum\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Exception\InvalidArgumentException');
        
        $this->assertTrue($rc->implementsInterface('Payum\Exception\ExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Exception\InvalidArgumentException');

        $this->assertTrue($rc->isSubclassOf('InvalidArgumentException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new InvalidArgumentException;
    }
}
