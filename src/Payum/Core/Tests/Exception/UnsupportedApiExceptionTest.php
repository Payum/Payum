<?php

namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\UnsupportedApiException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UnsupportedApiExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new ReflectionClass(UnsupportedApiException::class);

        $this->assertTrue($rc->isSubclassOf(InvalidArgumentException::class));
    }
}
