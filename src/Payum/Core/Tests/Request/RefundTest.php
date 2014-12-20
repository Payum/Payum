<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Refund;

class RefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Refund');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Refund(new \stdClass());
    }
}
