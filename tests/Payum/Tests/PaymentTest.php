<?php
namespace Payum\Tests;

use Payum\ApiAwareInterface;
use Payum\Exception\UnsupportedApiException;
use Payum\Payment;
use Payum\Action\ActionInterface;
use Payum\Action\PaymentAwareAction;
use Payum\Request\InteractiveRequestInterface;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentInterface()
    {
        $rc = new \ReflectionClass('Payum\Payment');
        
        $this->assertTrue($rc->implementsInterface('Payum\PaymentInterface'));
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
        $payment = new Payment;

        $this->assertAttributeInstanceOf('Payum\Extension\ExtensionCollection', 'extensions', $payment);
    }

    /**
     * @test
     */
    public function shouldAllowAddExtension()
    {
        $payment = new Payment;

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
        
        $payment = new Payment;

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

        $payment = new Payment;

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
    public function shouldSetFirstApiToActionApiAware()
    {
        $payment = new Payment();

        $payment->addApi($firstApi = new \stdClass());
        $payment->addApi($secondApi = new \stdClass());
        
        $action = $this->getMockForAbstractClass('Payum\Tests\ApiAwareAction');
        $action
            ->expects($this->once())
            ->method('setApi')
            ->with($this->identicalTo($firstApi))
        ;

        $payment->addAction($action);
    }

    /**
     * @test
     */
    public function shouldSetSecondApiToActionApiAwareIfFirstUnsupported()
    {
        $payment = new Payment();

        $payment->addApi($firstApi = new \stdClass());
        $payment->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass('Payum\Tests\ApiAwareAction');
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

        $payment->addAction($action);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage Cannot find right api supported by
     */
    public function throwIfPaymentNotHaveApiSupportedByAction()
    {
        $payment = new Payment();

        $payment->addApi($firstApi = new \stdClass());
        $payment->addApi($secondApi = new \stdClass());

        $action = $this->getMockForAbstractClass('Payum\Tests\ApiAwareAction');
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

        $payment->addAction($action);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
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
    public function shouldCatchInteractiveRequestThrownAndReturnIfInteractiveRequestSetTrue()
    {
        $expectedInteractiveRequest = $this->createInteractiveRequestMock();
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
            ->will($this->throwException($expectedInteractiveRequest))
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);

        $actualInteractiveRequest = $payment->execute($request, $isInteractiveRequestExpected = true);
        
        $this->assertSame($expectedInteractiveRequest, $actualInteractiveRequest);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Request\BaseInteractiveRequest
     */
    public function shouldNotCatchInteractiveRequestByDefault()
    {
        $firstRequest = new \stdClass();
        $secondRequest = new \stdClass();
        $interactiveRequest = $this->createInteractiveRequestMock();
        
        $firstAction = new RequireOtherRequestAction();
        $firstAction->setSupportedRequest($firstRequest);
        $firstAction->setRequiredRequest($secondRequest);
        
        $secondAction = new ThrowInteractiveAction();
        $secondAction->setSupportedRequest($secondRequest);
        $secondAction->setInteractiveRequest($interactiveRequest);

        $payment = new Payment();
        $payment->addAction($firstAction);
        $payment->addAction($secondAction);

        $payment->execute($firstRequest);
    }

    /**
     * @test
     */
    public function shouldSetPaymentToActionIfActionAwareOfPayment()
    {
        $payment = new Payment();

        $actionMock = $this->getMock('Payum\Action\PaymentAwareAction');
        $actionMock
            ->expects($this->once())
            ->method('setPayment')
            ->with($this->identicalTo($payment))
        ;
        
        $payment->addAction($actionMock);
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
        $expectedRequest = new \stdClass;
        
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
    public function shouldCallExtensionOnInteractiveRequestWhenInteractiveRequestThrown()
    {
        $expectedInteractiveRequestMock = $this->createInteractiveRequestMock();
        $expectedRequest = new \stdClass;

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($expectedInteractiveRequestMock))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onInteractiveRequest')
            ->with(
                $this->identicalTo($expectedInteractiveRequestMock),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($actionMock)
            )
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $actualInteractiveRequest = $payment->execute($expectedRequest, true);
        
        $this->assertSame($expectedInteractiveRequestMock, $actualInteractiveRequest);
    }

    /**
     * @test
     */
    public function shouldReturnNewInteractiveRequestProvidedByExtension()
    {
        $thrownInteractiveRequestMock = $this->createInteractiveRequestMock();
        $expectedInteractiveRequestMock = $this->createInteractiveRequestMock();
        $expectedRequest = new \stdClass;

        $actionMock = $this->createActionMock();
        $actionMock
            ->expects($this->once())
            ->method('execute')
            ->will($this->throwException($thrownInteractiveRequestMock))
        ;
        $actionMock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true))
        ;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onInteractiveRequest')
            ->with(
                $this->identicalTo($thrownInteractiveRequestMock),
                $this->identicalTo($expectedRequest),
                $this->identicalTo($actionMock)
            )
            ->will($this->returnValue($expectedInteractiveRequestMock))
        ;

        $payment = new Payment();
        $payment->addAction($actionMock);
        $payment->addExtension($extensionMock);

        $actualInteractiveRequest = $payment->execute($expectedRequest, true);

        $this->assertSame($expectedInteractiveRequestMock, $actualInteractiveRequest);
    }

    /**
     * @test
     */
    public function shouldCallExtensionOnPreExecute()
    {
        $expectedRequest = new \stdClass;

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
        $expectedRequest = new \stdClass;

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
        $expectedRequest = new \stdClass;

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
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function shouldCallExtensionOnExceptionWhenExceptionThrown()
    {
        $notSupportedRequest = new \stdClass;

        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->once())
            ->method('onException')
            ->with(
                $this->isInstanceOf('Payum\Exception\RequestNotSupportedException'),
                $this->identicalTo($notSupportedRequest)
            )
        ;

        $payment = new Payment();
        $payment->addExtension($extensionMock);

        $payment->execute($notSupportedRequest);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Request\InteractiveRequestInterface
     */
    protected function createInteractiveRequestMock()
    {
        return $this->getMock('Payum\Request\BaseInteractiveRequest');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Extension\ExtensionInterface
     */
    protected function createExtensionMock()
    {
        return $this->getMock('Payum\Extension\ExtensionInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Action\ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Action\ActionInterface');
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

class ThrowInteractiveAction implements ActionInterface
{
    protected $supportedRequest;
    
    protected $interactiveRequest;
    
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
    public function setInteractiveRequest(InteractiveRequestInterface $request)
    {
        $this->interactiveRequest = $request;
    }
    
    public function execute($request)
    {
        throw $this->interactiveRequest;
    }

    public function supports($request)
    {
        return $this->supportedRequest === $request;
    }
}

abstract class ApiAwareAction implements ActionInterface, ApiAwareInterface
{
    
}