<?php
namespace Payum\Tests\Exception;

use Payum\Exception\RuntimeException;

class RuntimeExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Exception\RuntimeException');
        
        $this->assertTrue($rc->implementsInterface('Payum\Exception\ExceptionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfRuntimeException()
    {
        $rc = new \ReflectionClass('Payum\Exception\RuntimeException');

        $this->assertTrue($rc->isSubclassOf('RuntimeException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RuntimeException;
    }
}
