<?php

declare(strict_types=1);

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ConfirmOrderTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(ConfirmOrder::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
