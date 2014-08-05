<?php
namespace Payum\Payex\Tests\Request\Api;

class StopRecurringPaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelAware()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\StopRecurringPayment');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelAware'));
    }
}