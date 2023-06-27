<?php

namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class CaptureOffsiteActionTest extends GenericActionTest
{
    /**
     * @var class-string<CaptureOffsiteAction>
     */
    protected $actionClass = CaptureOffsiteAction::class;

    /**
     * @var class-string<Capture>
     */
    protected $requestClass = Capture::class;

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testThrowIfUnsupportedApiGiven(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new CaptureOffsiteAction();

        $action->setApi(new stdClass());
    }

    public function testShouldRedirectToBe2billSiteIfExecCodeNotPresentInQuery(): void
    {
        $this->expectException(HttpPostRedirect::class);
        $model = [
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Gateway for digital stuff',
            'ORDERID' => 'orderId',
            'EXTRADATA' => '[]',
        ];

        $postArray = array_replace($model, [
            'HASH' => 'foobarbaz',
        ]);

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('prepareOffsitePayment')
            ->with($model)
            ->willReturn($postArray)
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
        ;

        $action = new CaptureOffsiteAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture($model);

        $action->execute($request);
    }

    public function testShouldUpdateModelWhenComeBackFromBe2billSite(): void
    {
        $model = [
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Gateway for digital stuff',
            'ORDERID' => 'orderId',
        ];

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('prepareOffsitePayment')
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->willReturnCallback(function (GetHttpRequest $request): void {
                $request->query['EXECCODE'] = 1;
                $request->query['FOO'] = 'fooVal';
            })
        ;

        $action = new CaptureOffsiteAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture($model);

        $action->execute($request);

        $actualModel = $request->getModel();

        $this->assertArrayHasKey('EXECCODE', $actualModel);

        $this->assertArrayHasKey('FOO', $actualModel);
        $this->assertSame('fooVal', $actualModel['FOO']);

        $this->assertArrayHasKey('CLIENTIDENT', $actualModel);
        $this->assertSame($model['CLIENTIDENT'], $actualModel['CLIENTIDENT']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
