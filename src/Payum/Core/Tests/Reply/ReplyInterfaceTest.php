<?php

namespace Payum\Core\Tests\Reply;

use Payum\Core\Exception\ExceptionInterface;
use Payum\Core\Reply\ReplyInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReplyInterfaceTest extends TestCase
{
    public function testShouldImplementExceptionInterface()
    {
        $rc = new ReflectionClass(ReplyInterface::class);

        $this->assertTrue($rc->implementsInterface(ExceptionInterface::class));
    }
}
