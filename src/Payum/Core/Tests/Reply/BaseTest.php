<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Reply;

use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\ReplyInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class BaseTest extends TestCase
{
    public function testShouldImplementReplyInterface(): void
    {
        $rc = new ReflectionClass(Base::class);

        $this->assertTrue($rc->implementsInterface(ReplyInterface::class));
    }

    public function testShouldBeSubClassOfLogicException(): void
    {
        $rc = new ReflectionClass(Base::class);

        $this->assertTrue($rc->isSubclassOf(LogicException::class));
    }
}
