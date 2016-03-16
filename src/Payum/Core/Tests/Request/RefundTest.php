<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Refund;

class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Refund::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Refund(new \stdClass());
    }
}
