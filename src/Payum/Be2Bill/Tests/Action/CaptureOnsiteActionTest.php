<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Be2Bill\Action\CaptureOnsiteAction;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpPostRedirect;

class CaptureOnsiteActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\CaptureOnsiteAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\CaptureOnsiteAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureOnsiteAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureWithArrayAccessAsModel()
    {
        $action = new CaptureOnsiteAction();

        $model = new \ArrayObject(array());

        $request = new Capture($model);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCapture()
    {
        $action = new CaptureOnsiteAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureAndNotArrayAsModel()
    {
        $action = new CaptureOnsiteAction();

        $request = new Capture(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureOnsiteAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new CaptureOnsiteAction();
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
        $action = new CaptureOnsiteAction();

        $action->setApi(new \stdClass);
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
            'DESCRIPTION' => 'Payment for digital stuff',
            'ORDERID' => 'orderId',
        );

        $postArray = array_replace($model, array(
            'HASH' => 'foobarbaz',
        ));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('prepareOnsitePayment')
            ->with($model)
            ->will($this->returnValue($postArray))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Http\GetRequestRequest'))
        ;

        $action = new CaptureOnsiteAction();
        $action->setApi($apiMock);
        $action->setPayment($paymentMock);

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
            'DESCRIPTION' => 'Payment for digital stuff',
            'ORDERID' => 'orderId',
        );

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('prepareOnsitePayment')
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Http\GetRequestRequest'))
            ->will($this->returnCallback(function(GetHttpRequest $request) {
                $request->query['EXECCODE'] = 1;
                $request->query['FOO'] = 'fooVal';
            }))
        ;

        $action = new CaptureOnsiteAction();
        $action->setApi($apiMock);
        $action->setPayment($paymentMock);

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
        return $this->getMock('Payum\Be2Bill\Api', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
