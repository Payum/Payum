<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;

class ConfirmOrderTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(ConfirmOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
