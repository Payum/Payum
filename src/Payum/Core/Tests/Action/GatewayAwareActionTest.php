<?php
namespace Payum\Core\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;

class GatewayAwareActionTest extends \PHPUnit_Framework_TestCase
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
        $gateway = $this->getMock(GatewayInterface::class);

        $action = $this->getMockForAbstractClass(GatewayAwareAction::class);

        $action->setGateway($gateway);

        $this->assertAttributeSame($gateway, 'gateway', $action);
    }
}
