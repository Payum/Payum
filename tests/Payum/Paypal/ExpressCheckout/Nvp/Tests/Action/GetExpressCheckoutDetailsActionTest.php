<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Payment;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;

class GetExpressCheckoutDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\GetExpressCheckoutDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new GetExpressCheckoutDetailsAction();
    }

    /**
     * @test
     */
    public function shouldSupportGetExpressCheckoutDetailsRequest()
    {
        $action = new GetExpressCheckoutDetailsAction();
        $action->setPayment(new Payment($this->createApiMock()));
        
        $this->assertTrue($action->supports(new GetExpressCheckoutDetailsRequest(new PaymentInstruction)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetExpressCheckoutDetailsRequest()
    {
        $action = new GetExpressCheckoutDetailsAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetExpressCheckoutDetailsAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The token must be set. Have you run SetExpressCheckoutAction?
     */
    public function throwIfInstructionNotHaveTokenSetInInstruction()
    {
        $action = new GetExpressCheckoutDetailsAction();
        $action->setPayment(new Payment($this->createApiMock()));
        
        $request = new GetExpressCheckoutDetailsRequest(new PaymentInstruction);

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
            ->method('getExpressCheckoutDetails')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new GetExpressCheckoutDetailsAction();
        $action->setPayment(new Payment($apiMock));

        $request = new GetExpressCheckoutDetailsRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken($expectedToken = 'theToken');

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TOKEN', $fields);
        $this->assertEquals($expectedToken, $fields['TOKEN']);
    }

    /**
     * @test
     */
    public function shouldCallApiGetExpressCheckoutDetailsMethodAndUpdateInstructionFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getExpressCheckoutDetails')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'FIRSTNAME'=> 'theFirstname',
                    'EMAIL' => 'the@example.com'
                )));
                
                return $response;
            }))
        ;

        $action = new GetExpressCheckoutDetailsAction();
        $action->setPayment(new Payment($apiMock));

        $request = new GetExpressCheckoutDetailsRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken('aToken');

        $action->execute($request);
        
        $this->assertEquals('theFirstname', $request->getPaymentInstruction()->getFirstname());
        $this->assertEquals('the@example.com', $request->getPaymentInstruction()->getEmail());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}