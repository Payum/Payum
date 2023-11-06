<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;

class GetRecurringPaymentsProfileDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails');

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
