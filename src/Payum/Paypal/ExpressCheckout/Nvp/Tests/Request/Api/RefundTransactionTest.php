<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\RefundTransaction;

class RefundTransactionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(RefundTransaction::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
