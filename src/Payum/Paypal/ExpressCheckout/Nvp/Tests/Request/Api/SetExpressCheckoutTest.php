<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

class SetExpressCheckoutTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(SetExpressCheckout::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
