<?php
namespace Payum\Payex\Tests\Request\Api;

class CheckRecurringPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CheckRecurringPayment');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
