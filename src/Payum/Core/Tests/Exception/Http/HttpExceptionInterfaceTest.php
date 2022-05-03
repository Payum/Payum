<?php
namespace Payum\Core\Tests\Exception\Http;

use PHPUnit\Framework\TestCase;

class HttpExceptionInterfaceTest extends TestCase
{
    public function testShouldImplementPayumExceptionInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Exception\Http\HttpExceptionInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }
}
