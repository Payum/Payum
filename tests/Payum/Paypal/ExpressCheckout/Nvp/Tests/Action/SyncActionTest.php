<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

class SyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new SyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestAndPaymentInstructionAsModel()
    {
        $action = new SyncAction();

        $request = new SyncRequest(new PaymentInstruction);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestPaymentInstructionAggregate()
    {
        $action = new SyncAction();

        $model = $this->getMock('Payum\PaymentInstructionAggregateInterface');
        $model
            ->expects($this->atLeastOnce())
            ->method('getPaymentInstruction')
            ->will($this->returnValue(new PaymentInstruction))
        ;

        $request = new SyncRequest($model);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSyncRequest()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfTokenNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;
        
        $action = new SyncAction();
        $action->setPayment($paymentMock);

        $request = new SyncRequest(new PaymentInstruction);
        
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldRequestGetExpressCheckoutDetailsIfTokenSetInInstruction()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest'))
        ;

        $action = new SyncAction();
        $action->setPayment($paymentMock);

        $instruction = new PaymentInstruction;
        $instruction->setToken('theToken');

        $action->execute(new SyncRequest($instruction));
    }

    /**
     * @test
     */
    public function shouldRequestGetTransactionDetailsTwice()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest'))
        ;
        $paymentMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest'))
        ;

        $action = new SyncAction();
        $action->setPayment($paymentMock);

        $instruction = new PaymentInstruction;
        $instruction->setToken('theToken');
        $instruction->setPaymentrequestTransactionid(0, 'fooTransId');
        $instruction->setPaymentrequestTransactionid(2, 'barTransId');

        $action->execute(new SyncRequest($instruction));
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
            ->expects($this->at(0))
            ->method('execute')
            ->will($this->throwException($ackFailedException))
        ;

        $action = new SyncAction();
        $action->setPayment($paymentMock);

        $instruction = new PaymentInstruction;
        $instruction->setLErrorcoden(100, 'theErrorCodeToBeCleaned');
        $instruction->setToken('aToken');

        $action->execute(new SyncRequest($instruction));

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