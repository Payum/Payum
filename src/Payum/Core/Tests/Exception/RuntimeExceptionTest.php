<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class RuntimeExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface(): void
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\RuntimeException::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException(): void
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\RuntimeException::class);

        $this->assertTrue($rc->isSubclassOf(RuntimeException::class));
    }
}
