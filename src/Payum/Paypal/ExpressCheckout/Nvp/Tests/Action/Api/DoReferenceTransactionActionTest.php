<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransactionRequest;

class DoReferenceTransactionActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new DoReferenceTransactionAction();
    }

    /**
     * @test
     */
    public function shouldSupportDoReferenceTransactionRequestAndArrayAccessAsModel()
    {
        $action = new DoReferenceTransactionAction();
        
        $this->assertTrue($action->supports(new DoReferenceTransactionRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoReferenceTransactionRequest()
    {
        $action = new DoReferenceTransactionAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DoReferenceTransactionAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage REFERENCEID must be set.
     */
    public function throwIfReferenceIdNotSetInModel()
    {
        $action = new DoReferenceTransactionAction();
        
        $action->execute(new DoReferenceTransactionRequest(array()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage PAYMENTACTION must be set.
     */
    public function throwIfPaymentActionNotSet()
    {
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransactionRequest(array(
            'REFERENCEID' => 'aReferenceId'
        ));

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage AMT must be set.
     */
    public function throwIfAmtNotSet()
    {
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransactionRequest(array(
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoReferenceTransactionMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;
        
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new DoReferenceTransactionAction();
        $action->setApi($apiMock);

        $request = new DoReferenceTransactionRequest(array(
            'REFERENCEID' => 'theReferenceId',
            'PAYMENTACTION' => 'theAction',
            'AMT' => 'theAmt'
        ));

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('REFERENCEID', $fields);
        $this->assertEquals('theReferenceId', $fields['REFERENCEID']);

        $this->assertArrayHasKey('AMT', $fields);
        $this->assertEquals('theAmt', $fields['AMT']);

        $this->assertArrayHasKey('PAYMENTACTION', $fields);
        $this->assertEquals('theAction', $fields['PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldCallApiDoReferenceTransactionMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'FIRSTNAME'=> 'theFirstname',
                    'EMAIL' => 'the@example.com'
                )));
                
                return $response;
            }))
        ;

        $action = new DoReferenceTransactionAction();
        $action->setApi($apiMock);

        $request = new DoReferenceTransactionRequest(array(
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
            'AMT' => 'anAmt'
        ));

        $action->execute($request);

        $model = $request->getModel();
        
        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertEquals('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
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