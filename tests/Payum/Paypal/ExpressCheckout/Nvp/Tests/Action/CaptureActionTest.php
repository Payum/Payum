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
    public function shouldSupportCaptureRequestAndInstructionNotSet()
    {
        $action = new CaptureAction();
        
        $model = new ModelWithInstruction();
        
        //guard
        $this->assertNull($model->getInstruction());
        
        $this->assertTrue($action->supports(new CaptureRequest($model)));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestAndInstructionSet()
    {
        $action = new CaptureAction();

        $model = new ModelWithInstruction();

        //guard
        $this->assertNull($model->getInstruction());

        $this->assertTrue($action->supports(new CaptureRequest($model)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new CaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
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

        $instruction = new PaymentInstruction;
        
        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action->execute(new CaptureRequest($model));
        
        $this->assertEquals(
            Api::PAYMENTACTION_SALE,
            $instruction->getPaymentrequestNPaymentaction(0)
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

        $instruction = new PaymentInstruction;

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);

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

        $instruction = new PaymentInstruction;
        $instruction->setToken('aToken');

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
        $action->execute(new CaptureRequest($model));
    }

    /**
     * @test
     */
    public function shouldRequestDoExpressCheckoutPaymentActionIfCheckoutStatusNotInitiatedSetInInstructionAndPayerIdSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $instruction = new PaymentInstruction;
        $instruction->setToken('aToken');
        $instruction->setPayerid('aPayerId');
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);

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

        $instruction = new PaymentInstruction;
        $instruction->setToken('aToken');
        $instruction->setPayerid(null);
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);
        
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

        $instruction = new PaymentInstruction;
        $instruction->setToken('aToken');
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);

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

        $instruction = new PaymentInstruction;
        $instruction->setLErrorcoden(100, 'theErrorCodeToBeCleaned');
        $instruction->setToken('aToken');
        $instruction->setCheckoutstatus(Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $model = new ModelWithInstruction();
        $model->setInstruction($instruction);

        $action->execute(new CaptureRequest($model));

        $this->assertEquals('foo_error', $instruction->getLErrorcoden(0));
        $this->assertEquals('bar_error', $instruction->getLErrorcoden(1));
        $this->assertNotContains('theErrorCodeToBeCleaned', $instruction->getLErrorcoden());
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}
