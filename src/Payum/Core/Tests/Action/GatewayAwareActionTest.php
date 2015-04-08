<?php
namespace Payum\Core\Tests\Action;

class GatewayAwareActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Action\GatewayAwareAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Action\GatewayAwareAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldSetGatewayToProperty()
    {
        $gateway = $this->getMock('Payum\Core\GatewayInterface');

        $action = $this->getMockForAbstractClass('Payum\Core\Action\GatewayAwareAction');

        $action->setGateway($gateway);

        $this->assertAttributeSame($gateway, 'gateway', $action);
    }
}
