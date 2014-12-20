<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;

class CheckOrderStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\CheckOrderStatus');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAsArgument()
    {
        new CheckOrderStatus(array());
    }
}
