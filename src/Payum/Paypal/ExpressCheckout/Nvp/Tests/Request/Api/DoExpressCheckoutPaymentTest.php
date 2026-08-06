<?php

declare(strict_types=1);

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DoExpressCheckoutPaymentTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(DoExpressCheckoutPayment::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
