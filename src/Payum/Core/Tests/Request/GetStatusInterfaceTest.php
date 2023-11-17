<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Request\GetStatusInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetStatusInterfaceTest extends TestCase
{
    public function testShouldImplementModelAwareInterface(): void
    {
        $rc = new ReflectionClass(GetStatusInterface::class);

        $this->assertTrue($rc->implementsInterface(ModelAwareInterface::class));
    }

    public function testShouldImplementModelAggregateInterface(): void
    {
        $rc = new ReflectionClass(GetStatusInterface::class);

        $this->assertTrue($rc->implementsInterface(ModelAggregateInterface::class));
    }
}
