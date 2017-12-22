<?php
namespace Payum\Core\Tests\Request;

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementReplyInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\Base');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Reply\ReplyInterface'));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfLogicException()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\Base');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Exception\LogicException'));
    }
}
