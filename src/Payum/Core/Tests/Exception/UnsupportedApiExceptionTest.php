<?php
namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\UnsupportedApiException;
use PHPUnit\Framework\TestCase;

class UnsupportedApiExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\UnsupportedApiException');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\InvalidArgumentException'));
    }
}
