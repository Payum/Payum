<?php

namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Invoice\Action\AuthorizeAction;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AuthorizeActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(AuthorizeAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportAuthorizeWithArrayAsModel(): void
    {
        $action = new AuthorizeAction();

        $this->assertTrue($action->supports(new Authorize([])));
    }

    public function testShouldNotSupportAnythingNotAuthorize(): void
    {
        $action = new AuthorizeAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportAuthorizeWithNotArrayAccessModel(): void
    {
        $action = new AuthorizeAction();

        $this->assertFalse($action->supports(new Authorize(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new AuthorizeAction();

        $action->execute(new stdClass());
    }

    public function testShouldSubExecuteReserveAmountIfRnoNotSet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ReserveAmount::class))
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize([]);

        $action->execute($request);
    }

    public function testShouldNotSubExecuteReserveAmountIfRnoAlreadySet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new AuthorizeAction();
        $action->setGateway($gatewayMock);

        $request = new Authorize([
            'rno' => 'aRno',
        ]);

        $action->execute($request);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
