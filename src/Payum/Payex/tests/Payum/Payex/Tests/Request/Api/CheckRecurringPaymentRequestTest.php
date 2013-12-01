<?php
namespace Payum\Payex\Tests\Request\Api;

class CheckRecurringPaymentRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CheckRecurringPaymentRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }
}