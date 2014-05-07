<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreementRequest;

class CreateBillingAgreementActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new CreateBillingAgreementAction();
    }

    /**
     * @test
     */
    public function shouldSupportCreateBillingAgreementRequestAndArrayAccessAsModel()
    {
        $action = new CreateBillingAgreementAction();
        
        $this->assertTrue($action->supports(new CreateBillingAgreementRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreateBillingAgreementRequest()
    {
        $action = new CreateBillingAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CreateBillingAgreementAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage TOKEN must be set. Have you run SetExpressCheckoutAction?
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new CreateBillingAgreementAction();
        
        $action->execute(new CreateBillingAgreementRequest(array()));
    }

    /**
     * @test
     */
    public function shouldCallApiCreateBillingAgreementMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;
        
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreementRequest(array(
            'TOKEN' => 'theToken'
        ));

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TOKEN', $fields);
        $this->assertEquals('theToken', $fields['TOKEN']);
    }

    /**
     * @test
     */
    public function shouldCallApiCreateBillingMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'FIRSTNAME'=> 'theFirstname',
                    'EMAIL' => 'the@example.com'
                )));
                
                return $response;
            }))
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreementRequest(array(
            'TOKEN' => 'aToken'
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