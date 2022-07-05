<?php

namespace Payum\Payex\Tests\Request\Api;

class StartRecurringPaymentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\StartRecurringPayment');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
