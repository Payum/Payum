<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Refund;
use PHPUnit\Framework\TestCase;

class RefundTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Refund::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
