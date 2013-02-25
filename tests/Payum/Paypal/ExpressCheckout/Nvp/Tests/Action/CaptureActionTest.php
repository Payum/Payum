<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Request\CaptureRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
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
    public function shouldSupportAuthorizeTokenRequestWithPaymentInstructionAsModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest(new PaymentInstruction)));
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
     * @expectedException \Payum\Exception\RequestNotSupportedException
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
        
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $request->getModel());
        $this->assertEquals(Api::PAYMENTACTION_SALE, $request->getModel()['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenNotSetInInstruction()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest'))
        ;
        
        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array()));
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
            ->with($this->isInstanceOf('Payum\Request\SyncRequest'))
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest'))
        ;
        $paymentMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
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
            ->with($this->isInstanceOf('Payum\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $model = new PaymentInstruction;
        $model->setToken('aToken');
        $model->setPayerid(null);
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

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
            ->with($this->isInstanceOf('Payum\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $model = new PaymentInstruction;
        $model->setToken('aToken');
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $action->execute(new CaptureRequest(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS
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
        
        $ackFailedException = new HttpResponseAckNotSuccessException(new FormRequest(), $response);
        
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

        $this->assertArrayHasKey('L_ERRORCODE0', $request->getModel());
        $this->assertEquals('foo_error', $request->getModel()['L_ERRORCODE0']);
        $this->assertArrayHasKey('L_ERRORCODE1', $request->getModel());
        $this->assertEquals('bar_error', $request->getModel()['L_ERRORCODE1']);
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}
