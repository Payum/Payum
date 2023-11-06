<?php
namespace Payum\Core\Tests\Reply;

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function testShouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\Base::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Reply\ReplyInterface::class));
    }

    public function testShouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass(\Payum\Core\Reply\Base::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Exception\LogicException::class));
    }
}
