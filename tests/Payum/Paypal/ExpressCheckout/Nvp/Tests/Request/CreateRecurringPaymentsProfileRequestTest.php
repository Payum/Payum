<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

class CreateRecurringPaymentsProfileRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\CreateRecurringPaymentsProfileRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }
}