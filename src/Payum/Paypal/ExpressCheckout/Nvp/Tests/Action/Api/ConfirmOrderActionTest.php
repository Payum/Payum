<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

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

    protected function setUp()
    {
        $this->action = new ConfirmOrderAction('theConfirmOrderTemplate');
    }

    public function couldBeConstructedWithoutAnyArguments()
    {
        //overwrite
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(ConfirmOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldRenderConfirmOrderTemplateIfHttpRequestNotPost()
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
                $this->assertEquals('theConfirmOrderTemplate', $request->getTemplateName());
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
            $this->assertEquals(200, $reply->getStatusCode());
            $this->assertEquals('thePage', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function shouldStillRenderConfirmOrderTemplateIfHttpRequestPostButWithoutConfirm()
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
                $this->assertEquals('theConfirmOrderTemplate', $request->getTemplateName());
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
            $this->assertEquals(200, $reply->getStatusCode());
            $this->assertEquals('thePage', $reply->getContent());

            return;
        }

        $this->fail('The exception is expected');
    }

    /**
     * @test
     */
    public function shouldGiveControllBackIfHttpRequestPostWithConfirm()
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
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
