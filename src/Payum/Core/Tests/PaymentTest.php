<?php
namespace Payum\Core\Tests;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Payment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Reply\ReplyInterface;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Payment');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Payment();
    }

    /**
     * @test
     */
    public function shouldCreateExtensionCollectionInstanceInConstructor()
    {
        $payment = new Payment();

        $this->assertAttributeInstanceOf('Payum\Core\Extension\ExtensionCollection', 'extensions', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowAddExtension()
    {
        $payment = new Payment();

        $payment->addExtension($this->createExtensionMock());

        $extensions = $this->readAttribute($payment, 'extensions');

        $this->assertAttributeCount(1, 'extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowAddActionAppendByDefault()
    {
        $expectedFirstAction = $this->createActionMock();
        $expectedSecondAction = $this->createActionMock();

        $payment = new Payment();

        $payment->addAction($expectedFirstAction);
        $payment->addAction($expectedSecondAction);

        $actualActions = $this->readAttribute($payment, 'actions');

        $this->assertInternalType('array', $actualActions);
        $this->assertCount(2, $actualActions);
        $this->assertSame($expectedFirstAction, $actualActions[0]);
        $this->assertSame($expectedSecondAction, $actualActions[1]);
    }

    /**
     * @test
     */
    public function shouldAllowAddActionWithPrependForced()
    {
        $expectedFirstAction = $this->createActionMock();
        $expectedSecondAction = $this->createActionMock();

        $payment = new Payment();

        $payment->addAction($expectedSecondAction);
        $payment->addAction($expectedFirstAction, $forcePrepend = true);

        $actualActions = $this->readAttribute($payment, 'actions');

        $this->assertInternalType('array', $actualActions);
        $this->assertCount(2, $actualActions);
        $this->assertSame($expectedFirstAction, $actualActions[0]);
        $this->assertSame($expectedSecondAction, $actualActions[1]);
    }

    /**
     * @test
     */
    public function shouldAllowAddApi()
    {
        $payment = new Payment();

        $payment->addApi(new \stdClass());
        $payment->addApi(new \stdClass());

        $this->assertAttributeCount(2, 'apis', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowAddApiWithPrependForced()
    {
        $expectedFirstApi = new \stdClass();
        $expectedSecondApi = new \stdClass();

        $payment = new Payment();

        $payment->addApi($expectedSecondApi);
        $payment->addApi($expectedFirstApi, $forcePrepend = true);

        $actualApis = $this->readAttribute($payment, 'apis');

        $this->assertInternalType('array', $actualApis);
        $this->assertCount(2, $actualApis);
        $this->assertSame($expectedFirstApi, $actualApis[0]);
        $this->assertSame($expectedSecondApi, $actualApis[1]);
    }

    /**
     * @test
     */
    public function shouldSetFirstApiToActionApiAwareOnExecute()
    {
        $payment = new Payment();

        $payment->addApi($firstApi = new \stdClass());
        $payment->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass('Payum\Core\Tests\ApiAwareAction');
        $action
            ->expects($this->at(0))
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
        ;

        $payment->addAction($action);

        $payment->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetSecondApiToActionApiAwareIfFirstUnsupportedOnExecute()
    {
        $payment = new Payment();

        $payment->addApi($firstApi = new \stdClass());
        $payment->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass('Payum\Core\Tests\ApiAwareAction');
        $action
            ->expects($this->at(0))
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
            ->will($this->throwException(new UnsupportedApiException('first api not supported')))
        ;
        $action
            ->expects($this->at(2))
            ->method('setApi')
            ->with($this->identicalTo($secondApi))
        ;

        $payment->addAction($action);

        $payment->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Cannot find right api supported by
     */
    public function throwIfPaymentNotHaveApiSupportedByActionOnExecute()
    {
        $payment = new Payment();

        $payment->addApi($firstApi = new \stdClass());
        $payment->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass('Payum\Core\Tests\ApiAwareAction');
        $action
            ->expects($this->at(0))
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
            ->will($this->throwException(new UnsupportedApiException('first api not supported')))
        ;
        $action
            ->expects($this->at(2))
            ->method('setApi')
            ->with($this->identicalTo($secondApi))
            ->will($this->throwException(new UnsupportedApiException('second api not supported')))
        ;

        $payment->addAction($action);

        $payment->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwRequestNotSupportedIfNoneActionSet()
    {
        $request = new \stdClass();

        $payment = new Payment();

        $payment->execute($request);
    }

    /**
     * @test
     */
    public function shouldProxyRequestToActionWhichSupportsRequest()
    {
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('supports')
            ->with($request)
            ->will($this->returnValue(true))
        ;
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->with($request)
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);

        $payment->execute($request);
    }

    /**
     * @test
     */
    public function shouldCatchReplyThrownAndReturnIfReplyCatchSetTrue()
    {
        $expectedReply = $this->createReplyMock();
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($expectedReply))
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);

        $actualReply = $payment->execute($request, $catchReply = true);

        $this->assertSame($expectedReply, $actualReply);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Reply\Base
     */
    public function shouldNotCatchReplyByDefault()
    {
        $firstRequest = new \stdClass();
        $secondRequest = new \stdClass();
        $replyMock = $this->createReplyMock();

        $firstAction = new RequireOtherRequestAction();
        $firstAction->setSupportedRequest($firstRequest);
        $firstAction->setRequiredRequest($secondRequest);

        $secondAction = new ThrowReplyAction();
        $secondAction->setSupportedRequest($secondRequest);
        $secondAction->setReply($replyMock);

        $payment = new Payment();
        $payment->addAction($firstAction);
        $payment->addAction($secondAction);

        $payment->execute($firstRequest);
    }

    /**
     * @test
     */
    public function shouldSetPaymentToActionIfActionAwareOfPaymentOnExecute()
    {
        $payment = new Payment();

        $actionMock = $this->getMock('Payum\Core\Action\PaymentAwareAction');
        $actionMock
            ->expects($this->at(0))
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $actionMock
            ->expects($this->at(1))
            ->method('setPayment')
            ->with($this->identicalTo($payment))
        ;

        $payment->addAction($actionMock);

        $payment->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage An error occurred
     */
    public function shouldCallExtensionOnExceptionWhenNotSupportedRequestThrown()
    {
        $expectedException = new \LogicException('An error occurred');
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($expectedException))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onException')
            ->with(
                $this->identicalTo($expectedException),
                $this->identicalTo($expectedRequest)
            )
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $payment->execute($expectedRequest);
    }

    /**
     * @test
     */
    public function shouldCallExtensionOnReplyWhenReplyThrown()
    {
        $expectedReplyMock = $this->createReplyMock();
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($expectedReplyMock))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onReply')
            ->with(
                $this->identicalTo($expectedReplyMock),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($actionMock)
            )
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $actualReply = $payment->execute($expectedRequest, true);

        $this->assertSame($expectedReplyMock, $actualReply);
    }

    /**
     * @test
     */
    public function shouldReturnNewReplyProvidedByExtension()
    {
        $thrownReplyMock = $this->createReplyMock();
        $expectedReplyMock = $this->createReplyMock();
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($thrownReplyMock))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onReply')
            ->with(
                $this->identicalTo($thrownReplyMock),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($actionMock)
            )
            ->will($this->returnValue($expectedReplyMock))
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $actualReply = $payment->execute($expectedRequest, true);

        $this->assertSame($expectedReplyMock, $actualReply);
    }

    /**
     * @test
     */
    public function shouldCallExtensionOnPreExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPreExecute')
            ->with(
                $this->identicalTo($expectedRequest)
            )
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $payment->execute($expectedRequest);
    }

    /**
     * @test
     */
    public function shouldCallExtensionOnExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onExecute')
            ->with(
                $this->identicalTo($expectedRequest),
                $this->identicalTo($actionMock)
            )
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $payment->execute($expectedRequest);
    }

    /**
     * @test
     */
    public function shouldCallExtensionOnPostExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with(
                $this->identicalTo($expectedRequest),
                $this->identicalTo($actionMock)
            )
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $payment->execute($expectedRequest);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function shouldCallExtensionOnExceptionWhenExceptionThrown()
    {
        $notSupportedRequest = new \stdClass();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onException')
            ->with(
                $this->isInstanceOf('Payum\Core\Exception\RequestNotSupportedException'),
                $this->identicalTo($notSupportedRequest)
            )
        ;

        $payment = new Payment();
        $payment->addExtension($extensionMock);

        $payment->execute($notSupportedRequest);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Reply\ReplyInterface
     */
    protected function createReplyMock()
    {
        return $this->getMock('Payum\Core\Reply\Base');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Extension\ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->getMock('Payum\Core\Extension\ExtensionInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\Action\ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }
}

class RequireOtherRequestAction extends PaymentAwareAction
{
    protected $supportedRequest;

    protected $requiredRequest;

    /**
     * @param $request
     */
    public function setSupportedRequest($request)
    {
        $this->supportedRequest = $request;
    }

    /**
     * @param $request
     */
    public function setRequiredRequest($request)
    {
        $this->requiredRequest = $request;
    }

    public function execute($request)
    {
        $this->payment->execute($this->requiredRequest);
    }

    public function supports($request)
    {
        return $this->supportedRequest === $request;
    }
}

class ThrowReplyAction implements ActionInterface
{
    protected $supportedRequest;

    protected $reply;

    /**
     * @param $request
     */
    public function setSupportedRequest($request)
    {
        $this->supportedRequest = $request;
    }

    /**
     * @param $request
     */
    public function setReply(ReplyInterface $request)
    {
        $this->reply = $request;
    }

    public function execute($request)
    {
        throw $this->reply;
    }

    public function supports($request)
    {
        return $this->supportedRequest === $request;
    }
}

abstract class ApiAwareAction implements ActionInterface, ApiAwareInterface
{
}
