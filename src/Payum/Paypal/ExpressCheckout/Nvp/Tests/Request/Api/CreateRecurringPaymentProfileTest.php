<?php

declare(strict_types=1);

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CreateRecurringPaymentProfileTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(CreateRecurringPaymentProfile::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
