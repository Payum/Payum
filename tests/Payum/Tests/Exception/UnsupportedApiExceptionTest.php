<?php
namespace Payum\Tests\Exception;

use Payum\Exception\UnsupportedApiException;

class UnsupportedApiExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass('Payum\Exception\UnsupportedApiException');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Exception\InvalidArgumentException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new UnsupportedApiException;
    }
}
