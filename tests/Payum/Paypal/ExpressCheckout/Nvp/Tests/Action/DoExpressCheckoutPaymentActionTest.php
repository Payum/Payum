<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;

class DoExpressCheckoutPaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithApiArgument()   
    {
        new DoExpressCheckoutPaymentAction($this->createApiMock());
    }

    /**
     * @test
     */
    public function shouldSupportDoExpressCheckoutPaymentRequest()
    {
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());
        
        $this->assertTrue($action->supports(new DoExpressCheckoutPaymentRequest(new PaymentInstruction)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoExpressCheckoutPaymentRequest()
    {
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());

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
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());
        
        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The payerid must be set.
     */
    public function throwIfPayerIdNotSetInInstruction()
    {
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getInstruction()->setToken('aToken');

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
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setPayerid('aPayerId');

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
        $action = new DoExpressCheckoutPaymentAction($this->createApiMock());

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setPayerid('aPayerId');
        $request->getInstruction()->setPaymentrequestNPaymentaction(0, 'anAction');

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
        
        $action = new DoExpressCheckoutPaymentAction($apiMock);

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getInstruction()->setToken($expectedToken = 'theToken');
        $request->getInstruction()->setPayerid($expectedPayerId = 'thePayerId');
        $request->getInstruction()->setPaymentrequestNPaymentaction(0, $expectedPaymentAction = 'theAction');
        $request->getInstruction()->setPaymentrequestNAmt(0, $expectedPaymentAmount = 'anAmt');

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

        $action = new DoExpressCheckoutPaymentAction($apiMock);

        $request = new DoExpressCheckoutPaymentRequest(new PaymentInstruction);
        $request->getInstruction()->setToken('aToken');
        $request->getInstruction()->setPayerid('aPayerId');
        $request->getInstruction()->setPaymentrequestNPaymentaction(0, 'anAction');
        $request->getInstruction()->setPaymentrequestNAmt(0, 'anAmt');

        $action->execute($request);
        
        $this->assertEquals('theFirstname', $request->getInstruction()->getFirstname());
        $this->assertEquals('the@example.com', $request->getInstruction()->getEmail());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}