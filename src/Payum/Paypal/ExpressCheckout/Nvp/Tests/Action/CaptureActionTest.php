<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Core\Model\Token;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\SecuredCaptureRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new CaptureAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestAndArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $request = new CaptureRequest($this->getMock('ArrayAccess'));
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureRequest()
    {
        $action = new CaptureAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestAndNotArrayAccessAsModel()
    {
        $action = new CaptureAction();
        
        $request = new CaptureRequest(new \stdClass());
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetZeroPaymentActionAsSell()
    {
        $action = new CaptureAction();
        $action->setPayment($this->createPaymentMock());
        
        $action->execute($request = new CaptureRequest(array()));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertEquals(Api::PAYMENTACTION_SALE, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenNotSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckoutRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest'))
        ;
        
        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array()));
    }

    /**
     * @test
     */
    public function shouldSetTokenTargetUrlAsReturnUrlIfSecuredCaptureRequestPassedAndReturnUrlNotSet()
    {
        $testCase = $this;

        $expectedTargetUrl = 'theTargetUrl';

        $token = new Token;
        $token->setTargetUrl($expectedTargetUrl);
        $token->setDetails(array());

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckoutRequest'))
            ->will($this->returnCallback(function($request) use ($testCase, $expectedTargetUrl) {
                $model = $request->getModel();

                $this->assertEquals($expectedTargetUrl, $model['RETURNURL']);
            }))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $request = new SecuredCaptureRequest($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldSetTokenTargetUrlAsCancelUrlIfSecuredCaptureRequestPassedAndReturnUrlNotSet()
    {
        $testCase = $this;

        $expectedCancelUrl = 'theCancelUrl';

        $token = new Token;
        $token->setTargetUrl($expectedCancelUrl);
        $token->setDetails(array());

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckoutRequest'))
            ->will($this->returnCallback(function($request) use ($testCase, $expectedCancelUrl) {
                $model = $request->getModel();

                $this->assertEquals($expectedCancelUrl, $model['CANCELURL']);
            }))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $request = new SecuredCaptureRequest($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken'
        )));
    }

    /**
     * @test
     */
    public function shouldRequestDoExpressCheckoutPaymentActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPaymentRequest'))
        ;
        $paymentMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfPayerIdNotSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfCheckoutStatusOtherThenNotInitiatedSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfAmountZero()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 0,
        )));
    }

    /**
     * @test
     */
    public function shouldUpdateModelFromResponseInCaughtAckFailedException()
    {
        $response = new Response();
        $response->setContent(http_build_query(array(
            'L_ERRORCODE0' => 'foo_error',
            'L_ERRORCODE1' => 'bar_error',
        )));
        
        $ackFailedException = new HttpResponseAckNotSuccessException;
        $ackFailedException->setRequest(new FormRequest());
        $ackFailedException->setResponse($response);
        
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->exactly(1))
            ->method('execute')
            ->will($this->throwException($ackFailedException))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute($request = new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS
        )));

        $model = $request->getModel();

        $this->assertArrayHasKey('L_ERRORCODE0', $model);
        $this->assertEquals('foo_error', $model['L_ERRORCODE0']);

        $this->assertArrayHasKey('L_ERRORCODE1', $model);
        $this->assertEquals('bar_error', $model['L_ERRORCODE1']);
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
