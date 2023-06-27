<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreement;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CreateBillingAgreementTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CreateBillingAgreement::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
