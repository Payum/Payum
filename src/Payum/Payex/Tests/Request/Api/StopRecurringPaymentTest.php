<?php
namespace Payum\Payex\Tests\Request\Api;

class StopRecurringPaymentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\StopRecurringPayment');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
