<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Reply;

use Payum\Core\Exception\ExceptionInterface;
use Payum\Core\Reply\ReplyInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ReplyInterfaceTest extends TestCase
{
    public function testShouldImplementExceptionInterface(): void
    {
        $rc = new ReflectionClass(ReplyInterface::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }
}
