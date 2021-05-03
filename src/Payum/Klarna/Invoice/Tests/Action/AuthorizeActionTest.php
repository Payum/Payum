<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Invoice\Action\AuthorizeAction;
use PHPUnit\Framework\TestCase;

class AuthorizeActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeWithArrayAsModel()
    {
        $action = new AuthorizeAction();

        $this->assertTrue($action->supports(new Authorize(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorize()
    {
        $action = new AuthorizeAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAuthorizeWithNotArrayAccessModel()
    {
        $action = new AuthorizeAction();

        $this->assertFalse($action->supports(new Authorize(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AuthorizeAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteReserveAmountIfRnoNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\ReserveAmount'))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotSubExecuteReserveAmountIfRnoAlreadySet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize(array(
            'rno' => 'aRno',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
