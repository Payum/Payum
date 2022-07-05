<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DoReferenceTransactionTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new ReflectionClass(DoReferenceTransaction::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
