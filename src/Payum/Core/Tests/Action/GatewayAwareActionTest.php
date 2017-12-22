<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use PHPUnit\Framework\TestCase;

class GatewayAwareActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GatewayAwareAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(GatewayAwareAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSetGatewayToProperty()
    {
        $gateway = $this->createMock(GatewayInterface::class);

        $action = $this->getMockForAbstractClass(GatewayAwareAction::class);

        $action->setGateway($gateway);

        $this->assertAttributeSame($gateway, 'gateway', $action);
    }
}
