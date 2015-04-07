<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Invoice\Action\AuthorizeAction;

class AuthorizeActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\AuthorizeAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AuthorizeAction();
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
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
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
