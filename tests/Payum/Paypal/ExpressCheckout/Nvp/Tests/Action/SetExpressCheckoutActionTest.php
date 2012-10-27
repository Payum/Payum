<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;

class SetExpressCheckoutActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\SetExpressCheckoutAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithApiArgument()   
    {
        new SetExpressCheckoutAction($this->createApiMock());
    }

    /**
     * @test
     */
    public function shouldSupportSetExpressCheckoutRequest()
    {
        $action = new SetExpressCheckoutAction($this->createApiMock());
        
        $this->assertTrue($action->supports(new SetExpressCheckoutRequest(new Instruction)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new SetExpressCheckoutAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SetExpressCheckoutAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The zero paymentamt must be set.
     */
    public function throwIfInstructionNotHavePaymentAmountSetInInstruction()
    {
        $action = new SetExpressCheckoutAction($this->createApiMock());
        
        $request = new SetExpressCheckoutRequest(new Instruction);

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

        $request = new SetExpressCheckoutRequest(new Instruction);
        $request->getInstruction()->setPaymentrequestNAmt(0, $expectedAmount = 154.23);

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

        $action = new SetExpressCheckoutAction($apiMock);

        $request = new SetExpressCheckoutRequest(new Instruction);
        $request->getInstruction()->setPaymentrequestNAmt(0, 154.23);

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