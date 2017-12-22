<?php
namespace Payum\Core\Tests\Reply;

use PHPUnit\Framework\TestCase;

class ReplyInterfaceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementExceptionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Reply\ReplyInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Exception\ExceptionInterface'));
    }
}
