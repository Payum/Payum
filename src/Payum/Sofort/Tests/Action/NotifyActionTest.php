<?php

namespace Payum\Sofort\Tests\Action;

use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\GatewayInterface;
use Payum\Sofort\Action\NotifyAction;

class NotifyActionTest extends GenericActionTest
{
    protected $requestClass = Notify::class;

    protected $actionClass = NotifyAction::class;

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldSubExecuteSyncWithSameModelAndThrowHttpResponse()
    {
        $expectedModel = array('foo' => 'fooVal');

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new Notify($expectedModel));
        } catch (HttpResponse $response) {
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
