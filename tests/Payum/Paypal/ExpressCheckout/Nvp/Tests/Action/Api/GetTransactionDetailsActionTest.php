<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class GetTransactionDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseActionApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new GetTransactionDetailsAction();
    }

    /**
     * @test
     */
    public function shouldSupportGetTransactionDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetTransactionDetailsAction();
        
        $request = new GetTransactionDetailsRequest($this->getMock('ArrayAccess'), $paymentRequestN = 5);
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportGetTransactionDetailsRequestWithPaymentDetailsAsModel()
    {
        $action = new GetTransactionDetailsAction();

        $this->assertTrue($action->supports(new GetTransactionDetailsRequest(new PaymentDetails, $paymentRequestN = 5)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetTransactionDetailsRequest()
    {
        $action = new GetTransactionDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetTransactionDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage PAYMENTREQUEST_5_TRANSACTIONID must be set.
     */
    public function throwIfZeroPaymentRequestTransactionIdNotSetInModel()
    {
        $action = new GetTransactionDetailsAction();
        
        $request = new GetTransactionDetailsRequest(array(), $paymentRequestN = 5);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetTransactionDetailsMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;
        
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetailsRequest(array(
            'PAYMENTREQUEST_5_TRANSACTIONID' => 'theTransactionId' 
        ), $paymentRequestN = 5);

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TRANSACTIONID', $fields);
        $this->assertEquals('theTransactionId', $fields['TRANSACTIONID']);
    }

    /**
     * @test
     */
    public function shouldCallApiGetTransactionDetailsAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'PAYMENTSTATUS' => 'theStatus',
                )));
                
                return $response;
            }))
        ;

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetailsRequest(array(
            'PAYMENTREQUEST_5_TRANSACTIONID' => 'aTransactionId'
        ), $paymentRequestN = 5);

        $action->execute($request);
        
        $this->assertArrayHasKey('PAYMENTREQUEST_5_PAYMENTSTATUS', $request->getModel());
        $this->assertEquals(
            'theStatus',
            $request->getModel()['PAYMENTREQUEST_5_PAYMENTSTATUS']
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}