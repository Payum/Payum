<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\NotifyPaymentAction;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Tests\GenericActionTest;

class NotifyPaymentActionTest extends GenericActionTest
{
    protected $actionClass = NotifyPaymentAction::class;

    protected $requestClass = Notify::class;

    protected function setUp()
    {
        $this->action = new $this->actionClass($this->createStorageMock(), 'idField');
    }

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(null)),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new $this->requestClass('foo')),
            array(new $this->requestClass([])),
            array(new $this->requestClass(new \ArrayObject([]))),
            array(new $this->requestClass(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        //overwrite
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(NotifyPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareAction::class));
    }

    /**
     * @test
     */
    public function throwIfQueryNotHaveOrderIdSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->query = ['order id is not set'];
            }))
        ;

        $action = new NotifyPaymentAction($this->createStorageMock(), 'idField');
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new Notify(null));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('The notification is invalid. Code 3', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function throwIfModelCouldNotBeFoundByOrderIdFromQuery()
    {
        $expectedId = 123;
        $expectedIdField = 'theIdField';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) use ($expectedId) {
                $request->query = ['ORDERID' => $expectedId];
            }))
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with($this->identicalTo([$expectedIdField => $expectedId]))
            ->willReturn(null)
        ;


        $action = new NotifyPaymentAction($storageMock, $expectedIdField);
        $action->setGateway($gatewayMock);

        try {
            $action->execute(new Notify(null));
        } catch (HttpResponse $reply) {
            $this->assertSame(400, $reply->getStatusCode());
            $this->assertSame('The notification is invalid. Code 4', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteWithPaymentFoundByOrderId()
    {
        $expectedId = 123;
        $expectedIdField = 'theIdField';
        $expectedModel = new \stdClass();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function(GetHttpRequest $request) use ($expectedId) {
                $request->query = ['ORDERID' => $expectedId];
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Notify::class))
            ->will($this->returnCallback(function(Notify $request) use ($expectedModel) {
                $this->assertSame($expectedModel, $request->getModel());
            }))
        ;

        $storageMock = $this->createStorageMock();
        $storageMock
            ->expects($this->once())
            ->method('findBy')
            ->with($this->identicalTo([$expectedIdField => $expectedId]))
            ->willReturn($expectedModel)
        ;


        $action = new NotifyPaymentAction($storageMock, $expectedIdField);
        $action->setGateway($gatewayMock);

        $action->execute(new Notify(null));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock(StorageInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
