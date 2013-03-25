<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;

class PaymentDetailsSyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new PaymentDetailsSyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestAndArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new PaymentDetailsSyncAction();

        $paymentDetails = array(
            'PAYMENTREQUEST_0_AMT' => 12
        );
        
        $request = new SyncRequest($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestAndArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new PaymentDetailsSyncAction();

        $paymentDetails = array(
            'PAYMENTREQUEST_0_AMT' => 0
        );

        $request = new SyncRequest($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestWithPaymentDetailsAsModel()
    {
        $action = new PaymentDetailsSyncAction();

        $paymentDetails = new PaymentDetails;
        $paymentDetails->setPaymentrequestAmt(0, 12);

        $this->assertTrue($action->supports(new SyncRequest($paymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSyncRequest()
    {
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsSyncAction();

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
        
        $action = new PaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $request = new SyncRequest(array(
            'PAYMENTREQUEST_0_AMT' => 12
        ));
        
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetailsRequest'))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $action->execute(new SyncRequest(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'TOKEN' => 'aToken',
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
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest'))
        ;
        $paymentMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest'))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $action->execute(new SyncRequest(array(
            'PAYMENTREQUEST_0_AMT' => 12,
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

        $action = new PaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $action->execute($request = new SyncRequest(array(
            'PAYMENTREQUEST_0_AMT' => 12,
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