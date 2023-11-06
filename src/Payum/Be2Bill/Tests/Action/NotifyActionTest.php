<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\NotifyAction;
use Payum\Be2Bill\Api;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = NotifyAction::class;

    protected $requestClass = Notify::class;

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new NotifyAction();

        $action->setApi(new \stdClass());
    }

    public function testThrowIfQueryHashDoesNotMatchExpected()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->query = ['expected be2bill query'];
            })
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyHash')
            ->willReturn(false)
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        try {
            $action->execute(new Notify([]));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('The notification is invalid. Code 1', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    public function testThrowIfQueryAmountDoesNotMatchOneFromModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->query = ['AMOUNT' => 2.0];
            })
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyHash')
            ->willReturn(true)
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        try {
            $action->execute(new Notify([
                'AMOUNT' => 1.0
            ]));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('The notification is invalid. Code 2', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    public function testShouldUpdateModelIfNotificationValid()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->query = ['AMOUNT' => 1.0, 'FOO' => 'FOO', 'BAR' => 'BAR'];
            })
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('verifyHash')
            ->willReturn(true)
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $model = new \ArrayObject([
            'AMOUNT' => 1.0,
            'FOO' => 'FOOOLD',
        ]);

        try {
            $action->execute(new Notify($model));
        } catch (HttpResponse $reply) {
            $this->assertSame([
                'AMOUNT' => 1.0,
                'FOO' => 'FOO',
                'BAR' => 'BAR',
            ], (array) $model);

            $this->assertSame(200, $reply->getStatusCode());
            $this->assertSame('OK', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, ['verifyHash'], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
