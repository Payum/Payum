<?php

namespace Payum\Sofort\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Sofort\Action\NotifyAction;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class NotifyActionTest extends GenericActionTest
{
    protected $requestClass = Notify::class;

    protected $actionClass = NotifyAction::class;

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSubExecuteSyncWithSameModelAndThrowHttpResponse(): void
    {
        $expectedModel = [
            'foo' => 'fooVal',
        ];

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
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
