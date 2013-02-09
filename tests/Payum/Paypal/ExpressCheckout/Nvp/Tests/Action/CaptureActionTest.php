<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Request\CaptureRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\ModelWithInstruction;

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
    public function shouldSupportCaptureRequestAndPaymentInstructionAsModel()
    {
        $action = new CaptureAction();

        $request = new CaptureRequest(new PaymentInstruction);
        
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
    public function shouldNotSupportCaptureRequestAndNoyPaymentInstructionAsModel()
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

        $model = new PaymentInstruction;
        
        $action->execute(new CaptureRequest($model));
        
        $this->assertEquals(
            Api::PAYMENTACTION_SALE,
            $model->getPaymentrequestPaymentaction(0)
        );
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

        $model = new PaymentInstruction;

        $action->execute(new CaptureRequest($model));
    }

    /**
     * @test
     */
    public function shouldNotRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenSetInInstruction()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $model = new PaymentInstruction;
        $model->setToken('aToken');

        $action->execute(new CaptureRequest($model));
    }

    /**
     * @test
     */
    public function shouldRequestDoExpressCheckoutPaymentActionIfCheckoutStatusNotInitiatedSetInInstructionAndPayerIdSet()
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $model = new PaymentInstruction;
        $model->setToken('aToken');
        $model->setPayerid('aPayerId');
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

        $action->execute(new CaptureRequest($model));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfPayerIdNotSetInInstruction()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $model = new PaymentInstruction;
        $model->setToken('aToken');
        $model->setPayerid(null);
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

        $action->execute(new CaptureRequest($model));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfCheckoutStatusOtherThenNotInitiatedSetInInstruction()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $model = new PaymentInstruction;
        $model->setToken('aToken');
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $action->execute(new CaptureRequest($model));
    }

    /**
     * @test
     */
    public function shouldUpdateInstructionFromResponseInCaughtAckFailedException()
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

        $model = new PaymentInstruction;
        $model->setLErrorcoden(100, 'theErrorCodeToBeCleaned');
        $model->setToken('aToken');
        $model->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $action->execute(new CaptureRequest($model));

        $this->assertEquals('foo_error', $model->getLErrorcoden(0));
        $this->assertEquals('bar_error', $model->getLErrorcoden(1));
        $this->assertNotContains('theErrorCodeToBeCleaned', $model->getLErrorcoden());
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}
