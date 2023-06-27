<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\RefundTransaction;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RefundTransactionTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(RefundTransaction::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
