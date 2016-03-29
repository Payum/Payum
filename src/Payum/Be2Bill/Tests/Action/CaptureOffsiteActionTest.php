<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Be2Bill\Action\CaptureOffsiteAction;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;

class CaptureOffsiteActionTest extends GenericActionTest
{
    protected $actionClass = CaptureOffsiteAction::class;

    protected $requestClass = Capture::class;

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureOffsiteAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new CaptureOffsiteAction();
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
        $action = new CaptureOffsiteAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Reply\HttpPostRedirect
     */
    public function shouldRedirectToBe2billSiteIfExecCodeNotPresentInQuery()
    {
        $model = array(
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Gateway for digital stuff',
            'ORDERID' => 'orderId',
            'EXTRADATA' => '[]',
        );

        $postArray = array_replace($model, array(
            'HASH' => 'foobarbaz',
        ));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('prepareOffsitePayment')
            ->with($model)
            ->will($this->returnValue($postArray))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
        ;

        $action = new CaptureOffsiteAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture($model);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldUpdateModelWhenComeBackFromBe2billSite()
    {
        $model = array(
            'AMOUNT' => 1000,
            'CLIENTIDENT' => 'payerId',
            'DESCRIPTION' => 'Gateway for digital stuff',
            'ORDERID' => 'orderId',
        );

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('prepareOffsitePayment')
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->query['EXECCODE'] = 1;
                $request->query['FOO'] = 'fooVal';
            }))
        ;

        $action = new CaptureOffsiteAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture($model);

        $action->execute($request);

        $actualModel = $request->getModel();

        $this->assertTrue(isset($actualModel['EXECCODE']));

        $this->assertTrue(isset($actualModel['FOO']));
        $this->assertEquals('fooVal', $actualModel['FOO']);

        $this->assertTrue(isset($actualModel['CLIENTIDENT']));
        $this->assertEquals($model['CLIENTIDENT'], $actualModel['CLIENTIDENT']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
