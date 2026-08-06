<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Exception\Http;

use Payum\Core\Exception\ExceptionInterface;
use Payum\Core\Exception\Http\HttpExceptionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HttpExceptionInterfaceTest extends TestCase
{
    public function testShouldImplementPayumExceptionInterface(): void
    {
        $rc = new ReflectionClass(HttpExceptionInterface::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }
}
