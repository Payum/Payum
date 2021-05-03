<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\ReturnAmount;

class ReturnAmountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\ReturnAmount');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
