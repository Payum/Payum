<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\UnsupportedApiException;

class UnsupportedApiExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\UnsupportedApiException');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\InvalidArgumentException'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new UnsupportedApiException();
    }
}
