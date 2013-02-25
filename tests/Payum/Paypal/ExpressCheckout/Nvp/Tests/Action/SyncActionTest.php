<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction;
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
    public function shouldSupportSyncRequestAndArrayAccessAsModel()
    {
        $action = new SyncAction();

        $request = new SyncRequest($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeTokenRequestWithPaymentInstructionAsModel()
    {
        $action = new SyncAction();

        $this->assertTrue($action->supports(new SyncRequest(new PaymentInstruction)));
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

        $request = new SyncRequest(array());
        
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldRequestGetExpressCheckoutDetailsIfTokenSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest'))
        ;

        $action = new SyncAction();
        $action->setPayment($paymentMock);

        $action->execute(new SyncRequest(array(
            'TOKEN' => 'aToken'
        )));
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

        $action->execute(new SyncRequest(array(
            'TOKEN' => 'aToken',
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'zeroTransId',
            'PAYMENTREQUEST_9_TRANSACTIONID' => 'nineTransId'
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
            ->expects($this->at(0))
            ->method('execute')
            ->will($this->throwException($ackFailedException))
        ;

        $action = new SyncAction();
        $action->setPayment($paymentMock);

        $action->execute($request = new SyncRequest(array(
            'TOKEN' => 'aToken',
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'aTransId',
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