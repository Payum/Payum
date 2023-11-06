<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ConfirmOrderAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;

class ConfirmOrderActionTest extends GenericActionTest
{
    protected $requestClass = ConfirmOrder::class;

    protected $actionClass = ConfirmOrderAction::class;

    protected function setUp(): void
    {
        $this->action = new ConfirmOrderAction('theConfirmOrderTemplate');
    }

    public function testShouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(ConfirmOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    public function testShouldRenderConfirmOrderTemplateIfHttpRequestNotPost()
    {
        $firstModel = new \stdClass();
        $model = new \ArrayObject(['foo' => 'fooVal', 'bar' => 'barVal']);


        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->method = 'GET';
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->willReturnCallback(function (RenderTemplate $request) use ($firstModel, $model) {
                $this->assertSame('theConfirmOrderTemplate', $request->getTemplateName());
                $this->assertSame($firstModel, $request->getParameters()['firstModel']);

                $this->assertInstanceOf(ArrayObject::class, $request->getParameters()['model']);
                $this->assertSame(['foo' => 'fooVal', 'bar' => 'barVal'], (array) $request->getParameters()['model']);

                $request->setResult('thePage');
            })
        ;

        $request = new ConfirmOrder($firstModel);
        $request->setModel($model);

        $this->action->setGateway($gatewayMock);

        try {
            $this->action->execute($request);
        } catch (HttpResponse $reply) {
            $this->assertSame(200, $reply->getStatusCode());
            $this->assertSame('thePage', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    public function testShouldStillRenderConfirmOrderTemplateIfHttpRequestPostButWithoutConfirm()
    {
        $firstModel = new \stdClass();
        $model = new \ArrayObject(['foo' => 'fooVal', 'bar' => 'barVal']);


        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
            })
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class))
            ->willReturnCallback(function (RenderTemplate $request) use ($firstModel, $model) {
                $this->assertSame('theConfirmOrderTemplate', $request->getTemplateName());
                $this->assertSame($firstModel, $request->getParameters()['firstModel']);

                $this->assertInstanceOf(ArrayObject::class, $request->getParameters()['model']);
                $this->assertSame(['foo' => 'fooVal', 'bar' => 'barVal'], (array) $request->getParameters()['model']);

                $request->setResult('thePage');
            })
        ;

        $request = new ConfirmOrder($firstModel);
        $request->setModel($model);

        $this->action->setGateway($gatewayMock);

        try {
            $this->action->execute($request);
        } catch (HttpResponse $reply) {
            $this->assertSame(200, $reply->getStatusCode());
            $this->assertSame('thePage', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    public function testShouldGiveControllBackIfHttpRequestPostWithConfirm()
    {
        $firstModel = new \stdClass();
        $model = new \ArrayObject(['foo' => 'fooVal', 'bar' => 'barVal']);


        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request = ['confirm' => 1];
            })
        ;

        $request = new ConfirmOrder($firstModel);
        $request->setModel($model);

        $this->action->setGateway($gatewayMock);

        $this->action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
