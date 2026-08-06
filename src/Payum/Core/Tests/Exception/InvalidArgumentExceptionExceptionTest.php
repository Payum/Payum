<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Exception;

use InvalidArgumentException;
use Payum\Core\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class InvalidArgumentExceptionExceptionTest extends TestCase
{
    public function testShouldImplementExceptionInterface(): void
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\InvalidArgumentException::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }

    public function testShouldBeSubClassOfRuntimeException(): void
    {
        $rc = new ReflectionClass(\Payum\Core\Exception\InvalidArgumentException::class);

        $this->assertTrue($rc->isSubclassOf(InvalidArgumentException::class));
    }
}
