<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Payment;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;

class DoExpressCheckoutPaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new DoExpressCheckoutPaymentAction();
    }

    /**
     * @test
     */
    public function shouldSupportDoExpressCheckoutPaymentRequestAndArrayAccessAsModel()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));
        
        $this->assertTrue($action->supports(new DoExpressCheckoutPaymentRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeTokenRequestWithPaymentInstructionAsModel()
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertTrue($action->supports(new DoExpressCheckoutPaymentRequest(new PaymentInstruction)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoExpressCheckoutPaymentRequest()
    {
        $action = new DoExpressCheckoutPaymentAction();
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
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage TOKEN must be set. Have you run SetExpressCheckoutAction?
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));
        
        $action->execute(new DoExpressCheckoutPaymentRequest(array()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage PAYERID must be set.
     */
    public function throwIfPayerIdNotSetInModel()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $request = new DoExpressCheckoutPaymentRequest(array(
            'TOKEN' => 'aToken'
        ));

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage PAYMENTREQUEST_0_PAYMENTACTION must be set.
     */
    public function throwIfZeroPaymentRequestActionNotSet()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $request = new DoExpressCheckoutPaymentRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId'
        ));

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage PAYMENTREQUEST_0_AMT must be set.
     */
    public function throwIfZeroPaymentRequestAmtNotSet()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $request = new DoExpressCheckoutPaymentRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'anAction',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;
        
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($apiMock));

        $request = new DoExpressCheckoutPaymentRequest(array(
            'TOKEN' => 'theToken',
            'PAYERID' => 'thePayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'theAction',
            'PAYMENTREQUEST_0_AMT' => 'theAmt'
        ));

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TOKEN', $fields);
        $this->assertEquals('theToken', $fields['TOKEN']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
        $this->assertEquals('theAmt', $fields['PAYMENTREQUEST_0_AMT']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $fields);
        $this->assertEquals('theAction', $fields['PAYMENTREQUEST_0_PAYMENTACTION']);

        $this->assertArrayHasKey('PAYERID', $fields);
        $this->assertEquals('thePayerId', $fields['PAYERID']);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'FIRSTNAME'=> 'theFirstname',
                    'EMAIL' => 'the@example.com'
                )));
                
                return $response;
            }))
        ;

        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($apiMock));

        $request = new DoExpressCheckoutPaymentRequest(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'anAction',
            'PAYMENTREQUEST_0_AMT' => 'anAmt'
        ));

        $action->execute($request);
        
        $this->assertArrayHasKey('FIRSTNAME', $request->getModel());
        $this->assertEquals('theFirstname', $request->getModel()['FIRSTNAME']);
        $this->assertArrayHasKey('EMAIL', $request->getModel());
        $this->assertEquals('the@example.com', $request->getModel()['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}