<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ConfirmOrderAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class ConfirmOrderActionTest extends GenericActionTest
{
    protected $requestClass = ConfirmOrder::class;

    protected $actionClass = ConfirmOrderAction::class;

    protected function setUp(): void
    {
        $this->action = new ConfirmOrderAction('theConfirmOrderTemplate');
    }

    public function testShouldBeSubClassOfGatewayAwareAction(): void
    {
        $rc = new ReflectionClass(ConfirmOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    public function testShouldRenderConfirmOrderTemplateIfHttpRequestNotPost(): void
    {
        $firstModel = new stdClass();
        $model = new \ArrayObject([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(RenderTemplate::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request): void {
                    $request->method = 'GET';
                }),
                $this->returnCallback(function (RenderTemplate $request) use ($firstModel, $model): void {
                    $this->assertSame('theConfirmOrderTemplate', $request->getTemplateName());
                    $this->assertSame($firstModel, $request->getParameters()['firstModel']);

                    $this->assertInstanceOf(ArrayObject::class, $request->getParameters()['model']);
                    $this->assertSame([
                        'foo' => 'fooVal',
                        'bar' => 'barVal',
                    ], (array) $request->getParameters()['model']);

                    $request->setResult('thePage');
                })
            )
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

    public function testShouldStillRenderConfirmOrderTemplateIfHttpRequestPostButWithoutConfirm(): void
    {
        $firstModel = new stdClass();
        $model = new \ArrayObject([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(RenderTemplate::class)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function (GetHttpRequest $request): void {
                    $request->method = 'POST';
                }),
                $this->returnCallback(function (RenderTemplate $request) use ($firstModel, $model): void {
                    $this->assertSame('theConfirmOrderTemplate', $request->getTemplateName());
                    $this->assertSame($firstModel, $request->getParameters()['firstModel']);

                    $this->assertInstanceOf(ArrayObject::class, $request->getParameters()['model']);
                    $this->assertSame([
                        'foo' => 'fooVal',
                        'bar' => 'barVal',
                    ], (array) $request->getParameters()['model']);

                    $request->setResult('thePage');
                })
            )
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

    public function testShouldGiveControllBackIfHttpRequestPostWithConfirm(): void
    {
        $firstModel = new stdClass();
        $model = new \ArrayObject([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request): void {
                $request->method = 'POST';
                $request->request = [
                    'confirm' => 1,
                ];
            })
        ;

        $request = new ConfirmOrder($firstModel);
        $request->setModel($model);

        $this->action->setGateway($gatewayMock);

        $this->action->execute($request);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
