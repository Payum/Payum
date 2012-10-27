<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SaleAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SaleRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

class SaleActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\SaleAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new SaleAction();
    }

    /**
     * @test
     */
    public function shouldSupportSaleRequest()
    {
        $action = new SaleAction();
        
        $this->assertTrue($action->supports(new SaleRequest(new Instruction)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSaleRequest()
    {
        $action = new SaleAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SaleAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetZeroPaymentActionAsSell()
    {
        $action = new SaleAction();
        $action->setPayment($this->createPaymentMock());

        $request = new SaleRequest(new Instruction);
        
        $action->execute($request);
        
        $this->assertEquals(
            Api::PAYMENTACTION_SALE,
            $request->getInstruction()->getPaymentrequestNPaymentaction(0)
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
        
        $action = new SaleAction();
        $action->setPayment($paymentMock);

        $action->execute(new SaleRequest(new Instruction));
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest'))
        ;

        $action = new SaleAction();
        $action->setPayment($paymentMock);

        $request = new SaleRequest(new Instruction);
        $request->getInstruction()->setToken('aToken');
        
        $action->execute($request);
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest'))
        ;
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

        $action = new SaleAction();
        $action->setPayment($paymentMock);

        $request = new SaleRequest(new Instruction);
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setPayerid('aPayerId');
        $request->getInstruction()->setCheckoutstatus(API::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

        $action->execute($request);
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new SaleAction();
        $action->setPayment($paymentMock);

        $request = new SaleRequest(new Instruction);
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setCheckoutstatus(API::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED);

        $action->execute($request);
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest'))
        ;

        $action = new SaleAction();
        $action->setPayment($paymentMock);

        $request = new SaleRequest(new Instruction);
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setCheckoutstatus(API::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $action->execute($request);
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

        $action = new SaleAction();
        $action->setPayment($paymentMock);

        $request = new SaleRequest(new Instruction);
        $request->getInstruction()->setLErrorcoden(100, 'theErrorCodeToBeCleaned');
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setCheckoutstatus(API::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS);

        $action->execute($request);

        $this->assertEquals('foo_error', $request->getInstruction()->getLErrorcoden(0));
        $this->assertEquals('bar_error', $request->getInstruction()->getLErrorcoden(1));
        $this->assertNotContains('theErrorCodeToBeCleaned', $request->getInstruction()->getLErrorcoden());
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}