<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckoutRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;

class SetExpressCheckoutActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new SetExpressCheckoutAction();
    }

    /**
     * @test
     */
    public function shouldSupportSetExpressCheckoutRequestAndArrayAccessAsModel()
    {
        $action = new SetExpressCheckoutAction();
        
        $request = new SetExpressCheckoutRequest($this->getMock('ArrayAccess'));
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSetExpressCheckoutRequestWithPaymentDetailsAsModel()
    {
        $action = new SetExpressCheckoutAction();

        $this->assertTrue($action->supports(new SetExpressCheckoutRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSetExpressCheckoutRequest()
    {
        $action = new SetExpressCheckoutAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SetExpressCheckoutAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The PAYMENTREQEUST_0_AMT must be set.
     */
    public function throwIfModelNotHavePaymentAmountSet()
    {
        $action = new SetExpressCheckoutAction();
        
        $request = new SetExpressCheckoutRequest(new \ArrayObject());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetExpressCheckoutDetailsMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;
        
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('setExpressCheckout')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new SetExpressCheckoutAction($apiMock);
        $action->setApi($apiMock);

        $request = new SetExpressCheckoutRequest(array(
            'PAYMENTREQUEST_0_AMT' => $expectedAmount = 154.23
        ));

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
        $this->assertEquals($expectedAmount, $fields['PAYMENTREQUEST_0_AMT']);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodAndUpdateInstructionFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('setExpressCheckout')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'FIRSTNAME'=> 'theFirstname',
                    'EMAIL' => 'the@example.com'
                )));
                
                return $response;
            }))
        ;

        $action = new SetExpressCheckoutAction();
        $action->setApi($apiMock);

        $request = new SetExpressCheckoutRequest(array(
            'PAYMENTREQUEST_0_AMT' => $expectedAmount = 154.23
        ));

        $action->execute($request);

        $model = $request->getModel();
        
        $this->assertEquals('theFirstname', $model['FIRSTNAME']);
        $this->assertEquals('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}