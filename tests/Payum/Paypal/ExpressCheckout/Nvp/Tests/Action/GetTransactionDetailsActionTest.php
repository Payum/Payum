<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class GetTransactionDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\GetTransactionDetailsAction');
        
        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithApiArgument()   
    {
        new GetTransactionDetailsAction($this->createApiMock());
    }

    /**
     * @test
     */
    public function shouldSupportGetTransactionDetailsRequest()
    {
        $action = new GetTransactionDetailsAction($this->createApiMock());
        
        $request = new GetTransactionDetailsRequest($paymentRequestN = 5, new Instruction);
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetTransactionDetailsRequest()
    {
        $action = new GetTransactionDetailsAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetTransactionDetailsAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The TransactionId must be set.
     */
    public function throwIfInstructionNotHaveTokenSetInInstruction()
    {
        $action = new GetTransactionDetailsAction($this->createApiMock());

        $request = new GetTransactionDetailsRequest($paymentRequestN = 5, new Instruction);
        
        //guard
        $this->assertNull($request->getInstruction()->getPaymentrequestNTransactionid($paymentRequestN));

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
        
        $action = new GetTransactionDetailsAction($apiMock);

        $request = new GetTransactionDetailsRequest($paymentRequestN = 5, new Instruction);
        $request->getInstruction()->setPaymentrequestNTransactionid(
            $paymentRequestN, 
            $expectedTransactionId = 'theTransactionId'
        );

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TRANSACTIONID', $fields);
        $this->assertEquals($expectedTransactionId, $fields['TRANSACTIONID']);
    }

    /**
     * @test
     */
    public function shouldCallApiGetTransactionDetailsAndUpdateInstructionFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'FIRSTNAME'=> 'theFirstname',
                    'EMAIL' => 'the@example.com',
                    'PAYMENTSTATUS' => 'theStatus',
                )));
                
                return $response;
            }))
        ;

        $action = new GetTransactionDetailsAction($apiMock);

        $request = new GetTransactionDetailsRequest($paymentRequestN = 5, new Instruction);
        $request->getInstruction()->setPaymentrequestNTransactionid(
            $paymentRequestN,
            $expectedTransactionId = 'theTransactionId'
        );

        $action->execute($request);
        
        $this->assertEquals('theFirstname', $request->getInstruction()->getFirstname());
        $this->assertEquals('the@example.com', $request->getInstruction()->getEmail());
        $this->assertEquals(
            'theStatus', 
            $request->getInstruction()->getPaymentrequestNPaymentstatus($paymentRequestN)
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