<?php
namespace Payum\Core\Tests;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Gateway;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Reply\ReplyInterface;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Gateway');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Gateway();
    }

    /**
     * @test
     */
    public function shouldCreateExtensionCollectionInstanceInConstructor()
    {
        $gateway = new Gateway();

        $this->assertAttributeInstanceOf('Payum\Core\Extension\ExtensionCollection', 'extensions', $gateway);
    }

    /**
     * @test
     */
    public function shouldAllowAddExtension()
    {
        $gateway = new Gateway();

        $gateway->addExtension($this->createExtensionMock());

        $extensions = $this->readAttribute($gateway, 'extensions');

        $this->assertAttributeCount(1, 'extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowAddActionAppendByDefault()
    {
        $expectedFirstAction = $this->createActionMock();
        $expectedSecondAction = $this->createActionMock();

        $gateway = new Gateway();

        $gateway->addAction($expectedFirstAction);
        $gateway->addAction($expectedSecondAction);

        $actualActions = $this->readAttribute($gateway, 'actions');

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

        $gateway = new Gateway();

        $gateway->addAction($expectedSecondAction);
        $gateway->addAction($expectedFirstAction, $forcePrepend = true);

        $actualActions = $this->readAttribute($gateway, 'actions');

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
        $gateway = new Gateway();

        $gateway->addApi(new \stdClass());
        $gateway->addApi(new \stdClass());

        $this->assertAttributeCount(2, 'apis', $gateway);
    }

    /**
     * @test
     */
    public function shouldAllowAddApiWithPrependForced()
    {
        $expectedFirstApi = new \stdClass();
        $expectedSecondApi = new \stdClass();

        $gateway = new Gateway();

        $gateway->addApi($expectedSecondApi);
        $gateway->addApi($expectedFirstApi, $forcePrepend = true);

        $actualApis = $this->readAttribute($gateway, 'apis');

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
        $gateway = new Gateway();

        $gateway->addApi($firstApi = new \stdClass());
        $gateway->addApi($secondApi = new \stdClass());

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

        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetSecondApiToActionApiAwareIfFirstUnsupportedOnExecute()
    {
        $gateway = new Gateway();

        $gateway->addApi($firstApi = new \stdClass());
        $gateway->addApi($secondApi = new \stdClass());

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

        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Cannot find right api supported by
     */
    public function throwIfGatewayNotHaveApiSupportedByActionOnExecute()
    {
        $gateway = new Gateway();

        $gateway->addApi($firstApi = new \stdClass());
        $gateway->addApi($secondApi = new \stdClass());

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

        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwRequestNotSupportedIfNoneActionSet()
    {
        $request = new \stdClass();

        $gateway = new Gateway();

        $gateway->execute($request);
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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);

        $gateway->execute($request);
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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);

        $actualReply = $gateway->execute($request, $catchReply = true);

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

        $gateway = new Gateway();
        $gateway->addAction($firstAction);
        $gateway->addAction($secondAction);

        $gateway->execute($firstRequest);
    }

    /**
     * @test
     */
    public function shouldSetGatewayToActionIfActionAwareOfGatewayOnExecute()
    {
        $gateway = new Gateway();

        $actionMock = $this->getMock('Payum\Core\Action\GatewayAwareAction');
        $actionMock
            ->expects($this->at(0))
            ->method('supports')
            ->will($this->returnValue(true))
        ;
        $actionMock
            ->expects($this->at(1))
            ->method('setGateway')
            ->with($this->identicalTo($gateway))
        ;

        $gateway->addAction($actionMock);

        $gateway->execute(new \stdClass());
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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest);
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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $actualReply = $gateway->execute($expectedRequest, true);

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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $actualReply = $gateway->execute($expectedRequest, true);

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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest);
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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest);
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

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest);
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

        $gateway = new Gateway();
        $gateway->addExtension($extensionMock);

        $gateway->execute($notSupportedRequest);
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

class RequireOtherRequestAction extends GatewayAwareAction
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
        $this->gateway->execute($this->requiredRequest);
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
