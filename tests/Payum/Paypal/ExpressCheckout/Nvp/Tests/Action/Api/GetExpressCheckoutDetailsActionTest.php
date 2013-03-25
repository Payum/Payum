<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetailsRequest;

class GetExpressCheckoutDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseActionApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware'));
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
    public function shouldSupportGetExpressCheckoutDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetExpressCheckoutDetailsAction();
        
        $this->assertTrue(
            $action->supports(new GetExpressCheckoutDetailsRequest($this->getMock('ArrayAccess')))
        );
    }

    /**
     * @test
     */
    public function shouldSupportGetExpressCheckoutDetailsRequestWithPaymentDetailsAsModel()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $this->assertTrue($action->supports(new GetExpressCheckoutDetailsRequest(new PaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetExpressCheckoutDetailsRequest()
    {
        $action = new GetExpressCheckoutDetailsAction();

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
        $action = new GetExpressCheckoutDetailsAction();
        
        $request = new GetExpressCheckoutDetailsRequest(array());

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
        $action->setApi($apiMock);

        $request = new GetExpressCheckoutDetailsRequest(array(
            'TOKEN' => 'theToken', 
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
    public function shouldCallApiGetExpressCheckoutDetailsMethodAndUpdateModelFromResponseOnSuccess()
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
        $action->setApi($apiMock);

        $request = new GetExpressCheckoutDetailsRequest(array(
            'TOKEN' => 'aToken',
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