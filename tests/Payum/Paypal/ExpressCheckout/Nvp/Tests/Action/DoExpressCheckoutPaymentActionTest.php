<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Payment;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;

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
    public function shouldSupportDoExpressCheckoutPaymentRequest()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));
        
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
     * @expectedExceptionMessage The token must be set. Have you run SetExpressCheckoutAction?
     */
    public function throwIfInstructionNotHaveTokenSetInInstruction()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));
        
        $action->execute(new DoExpressCheckoutPaymentRequest(new PaymentInstruction));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The payerid must be set.
     */
    public function throwIfPayerIdNotSetInInstruction()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken('aToken');

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The zero paymentaction must be set.
     */
    public function throwIfZeroPaymentRequestActionNotSet()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken('aToken');
        $request->getPaymentInstruction()->setPayerid('aPayerId');

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The zero paymentamt must be set.
     */
    public function throwIfZeroPaymentRequestAmtNotSet()
    {
        $action = new DoExpressCheckoutPaymentAction();
        $action->setPayment(new Payment($this->createApiMock()));

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken('aToken');
        $request->getPaymentInstruction()->setPayerid('aPayerId');
        $request->getPaymentInstruction()->setPaymentrequestPaymentaction(0, 'anAction');

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

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken($expectedToken = 'theToken');
        $request->getPaymentInstruction()->setPayerid($expectedPayerId = 'thePayerId');
        $request->getPaymentInstruction()->setPaymentrequestPaymentaction(0, $expectedPaymentAction = 'theAction');
        $request->getPaymentInstruction()->setPaymentrequestAmt(0, $expectedPaymentAmount = 'anAmt');

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TOKEN', $fields);
        $this->assertEquals($expectedToken, $fields['TOKEN']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
        $this->assertEquals($expectedPaymentAmount, $fields['PAYMENTREQUEST_0_AMT']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $fields);
        $this->assertEquals($expectedPaymentAction, $fields['PAYMENTREQUEST_0_PAYMENTACTION']);

        $this->assertArrayHasKey('PAYERID', $fields);
        $this->assertEquals($expectedPayerId, $fields['PAYERID']);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodAndUpdateInstructionFromResponseOnSuccess()
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

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getPaymentInstruction()->setToken('aToken');
        $request->getPaymentInstruction()->setPayerid('aPayerId');
        $request->getPaymentInstruction()->setPaymentrequestPaymentaction(0, 'anAction');
        $request->getPaymentInstruction()->setPaymentrequestAmt(0, 'anAmt');

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