<?php
namespace Payum\Payex\Tests\Request\Api;

class StopRecurringPaymentTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\StopRecurringPayment');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
