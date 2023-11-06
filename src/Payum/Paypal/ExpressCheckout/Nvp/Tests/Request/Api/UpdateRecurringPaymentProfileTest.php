<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;

class UpdateRecurringPaymentProfileTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile');

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
