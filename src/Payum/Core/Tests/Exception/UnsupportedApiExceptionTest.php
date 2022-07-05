<?php

namespace Payum\Core\Tests\Exception;

use PHPUnit\Framework\TestCase;

class UnsupportedApiExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfInvalidArgumentException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Exception\UnsupportedApiException::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Exception\InvalidArgumentException::class));
    }
}
