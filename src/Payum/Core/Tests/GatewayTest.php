<?php
namespace Payum\Core\Tests;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Gateway;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\Base;
use Payum\Core\Reply\ReplyInterface;

class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayInterface()
    {
        $rc = new \ReflectionClass(Gateway::class);

        $this->assertTrue($rc->implementsInterface(GatewayInterface::class));
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

        $this->assertAttributeInstanceOf(ExtensionCollection::class, 'extensions', $gateway);
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

        $action = $this->getMockForAbstractClass(ApiAwareAction::class);
        $action
            ->expects($this->at(0))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
        ;
        $action
            ->expects($this->at(1))
            ->method('supports')
            ->will($this->returnValue(true))
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

        $action = $this->getMockForAbstractClass(ApiAwareAction::class);
        $action
            ->expects($this->at(0))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
            ->will($this->throwException(new UnsupportedApiException('first api not supported')))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($secondApi))
        ;
        $action
            ->expects($this->at(2))
            ->method('supports')
            ->will($this->returnValue(true))
        ;


        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Cannot find right api for the action Mock_ApiAwareAction
     */
    public function throwIfGatewayNotHaveApiSupportedByActionOnExecute()
    {
        $gateway = new Gateway();

        $gateway->addApi($firstApi = new \stdClass());
        $gateway->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass(ApiAwareAction::class);
        $action
            ->expects($this->at(0))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
            ->will($this->throwException(new UnsupportedApiException('first api not supported')))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($secondApi))
            ->will($this->throwException(new UnsupportedApiException('second api not supported')))
        ;
        $action
            ->expects($this->never())
            ->method('supports')
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

        $actionMock = $this->getMock(GatewayAwareAction::class);
        $actionMock
            ->expects($this->at(0))
            ->method('setGateway')
            ->with($this->identicalTo($gateway))
        ;
        $actionMock
            ->expects($this->at(1))
            ->method('supports')
            ->will($this->returnValue(true))
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
    public function shouldCallOnPostExecuteWithExceptionWhenExceptionThrown()
    {
        $exception = new \LogicException('An error occurred');
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($exception))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($actionMock, $request, $exception, $gateway) {
                $this->assertSame($actionMock, $context->getAction());
                $this->assertSame($request, $context->getRequest());
                $this->assertSame($exception, $context->getException());
                $this->assertSame($gateway, $context->getGateway());
                $this->assertNull($context->getReply());
            })
        ;

        $gateway->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Another error.
     */
    public function shouldThrowNewExceptionProvidedByExtensionOnPostExecute()
    {
        $exception = new \LogicException('An error occurred');
        $newException = new \InvalidArgumentException('Another error.');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($exception))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($exception, $newException) {
                $this->assertSame($exception, $context->getException());

                $context->setException($newException);
            })
        ;

        $gateway->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotThrowNewExceptionIfUnsetByExtensionOnPostExecute()
    {
        $exception = new \LogicException('An error occurred');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($exception))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($exception) {
                $this->assertSame($exception, $context->getException());

                $context->setException(null);
            })
        ;

        $gateway->execute($request);
    }

    /**
     * @test
     */
    public function shouldReThrowNewExceptionProvidedThrownByExtensionOnPostExecuteIfPreviousOurException()
    {
        $exception = new \LogicException('An error occurred');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($exception))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($exception) {
                throw new \InvalidArgumentException('Another error.', null, $exception);
            })
        ;

        try {
            $gateway->execute($request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Another error.', $e->getMessage());
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('The exception is expected.');
    }

    /**
     * @test
     */
    public function shouldThrowOurExceptionAndIgnoreExceptionThrownFromExtension()
    {
        $exception = new \LogicException('An error occurred');
        $newException = new \InvalidArgumentException('Another error.');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($exception))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();

        $gateway = new Gateway();
        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($exception, $newException) {
                throw new \InvalidArgumentException('Another error.');
            })
        ;

        try {
            $gateway->execute($request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals('Another error.', $e->getMessage());
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('The exception is expected.');
    }

    /**
     * @test
     */
    public function shouldExecuteActionSetByExtensionOnExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->never())
            ->method('execute')
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $anotherActionMock = $this->createActionMock();
        $anotherActionMock
            ->expects($this->once())
            ->method('execute')
        ;

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($actionMock, $anotherActionMock) {
                $this->assertSame($actionMock, $context->getAction());

                $context->setAction($anotherActionMock);
            })
        ;

        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest, true);
    }

    /**
     * @test
     */
    public function shouldUseActionSetOnPreExecuteByExtensionOnExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
        ;
        $actionMock
            ->expects($this->never())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($actionMock) {
                $this->assertNull($context->getAction());

                $context->setAction($actionMock);
            })
        ;

        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest, true);
    }

    /**
     * @test
     */
    public function shouldCallOnPostExecuteWithReplyWhenReplyThrown()
    {
        $reply = $this->createReplyMock();
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($reply))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($actionMock, $request, $reply, $gateway) {
                $this->assertSame($actionMock, $context->getAction());
                $this->assertSame($request, $context->getRequest());
                $this->assertSame($gateway, $context->getGateway());
                $this->assertSame($reply, $context->getReply());
                $this->assertNull($context->getException());
            })
        ;

        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $actualReply = $gateway->execute($request, true);

        $this->assertSame($reply, $actualReply);
    }

    /**
     * @test
     */
    public function shouldReturnNewReplyProvidedByExtensionOnPostExecute()
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

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($thrownReplyMock, $expectedReplyMock) {
                $this->assertSame($thrownReplyMock, $context->getReply());

                $context->setReply($expectedReplyMock);
            })
        ;

        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $actualReply = $gateway->execute($expectedRequest, true);

        $this->assertSame($expectedReplyMock, $actualReply);
    }

    /**
     * @test
     */
    public function shouldNotReturnReplyIfUnsetOnPostExecute()
    {
        $thrownReplyMock = $this->createReplyMock();
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

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPostExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($thrownReplyMock) {
                $this->assertSame($thrownReplyMock, $context->getReply());

                $context->setReply(null);
            })
        ;

        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $actualReply = $gateway->execute($expectedRequest, true);

        $this->assertNull(null, $actualReply);
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

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onPreExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($expectedRequest, $actionMock, $gateway) {
                $this->assertSame($expectedRequest, $context->getRequest());
                $this->assertSame($gateway, $context->getGateway());

                $this->assertNull($context->getAction());
                $this->assertNull($context->getReply());
                $this->assertNull($context->getException());
            })
        ;

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

        $gateway = new Gateway();

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onExecute')
            ->with($this->isInstanceOf(Context::class))
            ->willReturnCallback(function (Context $context) use ($expectedRequest, $actionMock, $gateway) {
                $this->assertSame($expectedRequest, $context->getRequest());
                $this->assertSame($actionMock, $context->getAction());
                $this->assertSame($gateway, $context->getGateway());

                $this->assertNull($context->getReply());
                $this->assertNull($context->getException());
            })
        ;

        $gateway->addAction($actionMock);
        $gateway->addExtension($extensionMock);

        $gateway->execute($expectedRequest);
    }

    /**
     * @test
     */
    public function shouldPopulateContextWithPreviousOnesOnSubExecutes()
    {
        $gateway = new Gateway();

        $firstRequest = new \stdClass();
        $secondRequest = new \stdClass();

        $firstAction = new RequireOtherRequestAction();
        $firstAction->setSupportedRequest($firstRequest);
        $firstAction->setRequiredRequest($secondRequest);

        $gateway->addAction($firstAction);

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->willReturnCallback(function ($request) use ($secondRequest) {
                return $secondRequest === $request;
            })
        ;
        $gateway->addAction($actionMock);

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(0))
            ->method('onPreExecute')
            ->willReturnCallback(function (Context $context) use ($firstRequest) {
                $this->assertSame($firstRequest, $context->getRequest());

                $this->assertEmpty($context->getPrevious());
            })
        ;
        $extensionMock
            ->expects($this->at(1))
            ->method('onPreExecute')
            ->willReturnCallback(function (Context $context) use ($secondRequest) {
                $this->assertSame($secondRequest, $context->getRequest());

                $this->assertCount(1, $context->getPrevious());
                $this->assertContainsOnly(Context::class, $context->getPrevious());
            })
        ;

        $gateway->addExtension($extensionMock);

        $gateway->execute($firstRequest);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ReplyInterface
     */
    protected function createReplyMock()
    {
        return $this->getMock(Base::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->getMock(ExtensionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock(ActionInterface::class);
    }
}

abstract class GatewayAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
}

abstract class ApiAwareAction implements ActionInterface, ApiAwareInterface
{
}

class RequireOtherRequestAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    
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
