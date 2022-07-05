<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;

class DoReferenceTransactionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction');

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
