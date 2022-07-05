<?php

namespace Payum\Core\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\GatewayAwareInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GatewayAwareActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(GatewayAwareAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new ReflectionClass(GatewayAwareAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }
}
