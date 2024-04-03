<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Model\PayoutInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PayoutInterfaceTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface(): void
    {
        $rc = new ReflectionClass(PayoutInterface::class);

        $this->assertTrue($rc->implementsInterface(DetailsAwareInterface::class));
    }

    public function testShouldExtendDetailsAggregateInterface(): void
    {
        $rc = new ReflectionClass(PayoutInterface::class);

        $this->assertTrue($rc->implementsInterface(DetailsAggregateInterface::class));
    }
}
