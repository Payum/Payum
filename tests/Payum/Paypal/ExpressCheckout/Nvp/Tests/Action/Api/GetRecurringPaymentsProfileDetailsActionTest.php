<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetailsRequest;

class GetRecurringPaymentsProfileDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new GetRecurringPaymentsProfileDetailsAction();
    }

    /**
     * @test
     */
    public function shouldSupportGetRecurringPaymentsProfileDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();
        
        $this->assertTrue(
            $action->supports(new GetRecurringPaymentsProfileDetailsRequest($this->getMock('ArrayAccess')))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetRecurringPaymentsProfileDetailsRequest()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID fields is required.
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();
        
        $request = new GetRecurringPaymentsProfileDetailsRequest(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetRecurringPaymentsProfileDetailsMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;
        
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;
        
        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetailsRequest(array(
            'PROFILEID' => 'theProfileId', 
        ));

        $action->execute($request);
        
        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);
        
        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('PROFILEID', $fields);
        $this->assertEquals('theProfileId', $fields['PROFILEID']);
    }

    /**
     * @test
     */
    public function shouldCallApiGetRecurringPaymentsProfileDetailsMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'STATUS'=> 'theStatus',
                )));
                
                return $response;
            }))
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetailsRequest(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('STATUS', $model);
        $this->assertEquals('theStatus', $model['STATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}