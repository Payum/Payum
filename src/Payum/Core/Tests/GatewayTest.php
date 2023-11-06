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

class GatewayTest extends TestCase
{
    public function testShouldImplementGatewayInterface()
    {
        $rc = new \ReflectionClass(Gateway::class);

        $this->assertTrue($rc->implementsInterface(GatewayInterface::class));
    }

    public function testShouldAllowAddActionAppendByDefault()
    {
        $expectedFirstAction = $this->createActionMock();
        $expectedSecondAction = $this->createActionMock();

        $gateway = new Gateway();

        $gateway->addAction($expectedFirstAction);
        $gateway->addAction($expectedSecondAction);

        $actualActions = $this->readAttribute($gateway, 'actions');

        $this->assertIsArray($actualActions);
        $this->assertCount(2, $actualActions);
        $this->assertSame($expectedFirstAction, $actualActions[0]);
        $this->assertSame($expectedSecondAction, $actualActions[1]);
    }

    public function testShouldAllowAddActionWithPrependForced()
    {
        $expectedFirstAction = $this->createActionMock();
        $expectedSecondAction = $this->createActionMock();

        $gateway = new Gateway();

        $gateway->addAction($expectedSecondAction);
        $gateway->addAction($expectedFirstAction, $forcePrepend = true);

        $actualActions = $this->readAttribute($gateway, 'actions');

        $this->assertIsArray($actualActions);
        $this->assertCount(2, $actualActions);
        $this->assertSame($expectedFirstAction, $actualActions[0]);
        $this->assertSame($expectedSecondAction, $actualActions[1]);
    }

    public function testShouldAllowAddApi()
    {
        $gateway = new Gateway();

        $gateway->addApi(new \stdClass());
        $gateway->addApi(new \stdClass());

        $this->assertCount(2, $this->readAttribute($gateway, 'apis'));
    }

    public function testShouldAllowAddApiWithPrependForced()
    {
        $expectedFirstApi = new \stdClass();
        $expectedSecondApi = new \stdClass();

        $gateway = new Gateway();

        $gateway->addApi($expectedSecondApi);
        $gateway->addApi($expectedFirstApi, $forcePrepend = true);

        $actualApis = $this->readAttribute($gateway, 'apis');

        $this->assertIsArray($actualApis);
        $this->assertCount(2, $actualApis);
        $this->assertSame($expectedFirstApi, $actualApis[0]);
        $this->assertSame($expectedSecondApi, $actualApis[1]);
    }

    public function testShouldSetFirstApiToActionApiAwareOnExecute()
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
            ->willReturn(true)
        ;

        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    public function testShouldSetSecondApiToActionApiAwareIfFirstUnsupportedOnExecute()
    {
        $gateway = new Gateway();

        $gateway->addApi($firstApi = new \stdClass());
        $gateway->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass(ApiAwareAction::class);
        $action
            ->expects($this->at(0))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
            ->willThrowException(new UnsupportedApiException('first api not supported'))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($secondApi))
        ;
        $action
            ->expects($this->at(2))
            ->method('supports')
            ->willReturn(true)
        ;


        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    public function testThrowIfGatewayNotHaveApiSupportedByActionOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Cannot find right api for the action Mock_ApiAwareAction');
        $gateway = new Gateway();

        $gateway->addApi($firstApi = new \stdClass());
        $gateway->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass(ApiAwareAction::class);
        $action
            ->expects($this->at(0))
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
            ->willThrowException(new UnsupportedApiException('first api not supported'))
        ;
        $action
            ->expects($this->at(1))
            ->method('setApi')
            ->with($this->identicalTo($secondApi))
            ->willThrowException(new UnsupportedApiException('second api not supported'))
        ;
        $action
            ->expects($this->never())
            ->method('supports')
        ;


        $gateway->addAction($action);

        $gateway->execute(new \stdClass());
    }

    public function testThrowRequestNotSupportedIfNoneActionSet()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $request = new \stdClass();

        $gateway = new Gateway();

        $gateway->execute($request);
    }

    public function testShouldProxyRequestToActionWhichSupportsRequest()
    {
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('supports')
            ->with($request)
            ->willReturn(true)
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

    public function testShouldCatchReplyThrownAndReturnIfReplyCatchSetTrue()
    {
        $expectedReply = $this->createReplyMock();
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('supports')
            ->willReturn(true)
        ;
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($expectedReply)
        ;

        $gateway = new Gateway();
        $gateway->addAction($actionMock);

        $actualReply = $gateway->execute($request, $catchReply = true);

        $this->assertSame($expectedReply, $actualReply);
    }

    public function testShouldNotCatchReplyByDefault()
    {
        $this->expectException(\Payum\Core\Reply\Base::class);
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

    public function testShouldSetGatewayToActionIfActionAwareOfGatewayOnExecute()
    {
        $gateway = new Gateway();

        $actionMock = $this->createMock(GatewayAwareAction::class);
        $actionMock
            ->expects($this->at(0))
            ->method('setGateway')
            ->with($this->identicalTo($gateway))
        ;
        $actionMock
            ->expects($this->at(1))
            ->method('supports')
            ->willReturn(true)
        ;


        $gateway->addAction($actionMock);

        $gateway->execute(new \stdClass());
    }

    public function testShouldCallOnPostExecuteWithExceptionWhenExceptionThrown()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('An error occurred');
        $exception = new \LogicException('An error occurred');
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($exception)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldThrowNewExceptionProvidedByExtensionOnPostExecute()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Another error.');
        $exception = new \LogicException('An error occurred');
        $newException = new \InvalidArgumentException('Another error.');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($exception)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldNotThrowNewExceptionIfUnsetByExtensionOnPostExecute()
    {
        $exception = new \LogicException('An error occurred');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($exception)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldReThrowNewExceptionProvidedThrownByExtensionOnPostExecuteIfPreviousOurException()
    {
        $exception = new \LogicException('An error occurred');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($exception)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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
                throw new \InvalidArgumentException('Another error.', 0, $exception);
            })
        ;

        try {
            $gateway->execute($request);
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertSame('Another error.', $e->getMessage());
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('The exception is expected.');
    }

    public function testShouldThrowOurExceptionAndIgnoreExceptionThrownFromExtension()
    {
        $exception = new \LogicException('An error occurred');
        $newException = new \InvalidArgumentException('Another error.');

        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($exception)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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
            $this->assertSame('Another error.', $e->getMessage());
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('The exception is expected.');
    }

    public function testShouldExecuteActionSetByExtensionOnExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->never())
            ->method('execute')
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldUseActionSetOnPreExecuteByExtensionOnExecute()
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
            ->willReturn(true)
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

    public function testShouldCallOnPostExecuteWithReplyWhenReplyThrown()
    {
        $reply = $this->createReplyMock();
        $request = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($reply)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldReturnNewReplyProvidedByExtensionOnPostExecute()
    {
        $thrownReplyMock = $this->createReplyMock();
        $expectedReplyMock = $this->createReplyMock();
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($thrownReplyMock)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldNotReturnReplyIfUnsetOnPostExecute()
    {
        $thrownReplyMock = $this->createReplyMock();
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->willThrowException($thrownReplyMock)
        ;
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

        $this->assertNull($actualReply);
    }

    public function testShouldCallExtensionOnPreExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldCallExtensionOnExecute()
    {
        $expectedRequest = new \stdClass();

        $actionMock = $this->createActionMock();
        $actionMock
            ->method('supports')
            ->willReturn(true)
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

    public function testShouldPopulateContextWithPreviousOnesOnSubExecutes()
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
        return $this->createMock(Base::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->createMock(ExtensionInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->createMock(ActionInterface::class);
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
