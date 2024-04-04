<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Payout;
use PHPUnit\Framework\TestCase;

class PayoutTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Payout::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
