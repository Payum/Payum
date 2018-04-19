<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\UnsupportedApiException;
use PHPUnit\Framework\TestCase;

class UnsupportedApiExceptionTest extends TestCase
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
