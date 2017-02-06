<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\NotifyAction;
use Payum\Be2Bill\Api;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = NotifyAction::class;

    protected $requestClass = Notify::class;

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
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new NotifyAction();
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = new NotifyAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfQueryHashDoesNotMatchExpected()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->query = ['expected be2bill query'];
            }))
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

    /**
     * @test
     */
    public function throwIfQueryAmountDoesNotMatchOneFromModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->query = ['AMOUNT' => 2.0];
            }))
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

    /**
     * @test
     */
    public function shouldUpdateModelIfNotificationValid()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->query = ['AMOUNT' => 1.0, 'FOO' => 'FOO', 'BAR' => 'BAR'];
            }))
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
            $this->assertEquals([
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
        return $this->getMock(Api::class, ['verifyHash'], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
