<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatusRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails;

class ManageRecurringPaymentsProfileStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseActionApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new ManageRecurringPaymentsProfileStatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportManageRecurringPaymentsProfileStatusRequestAndArrayAccessAsModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $this->assertTrue(
            $action->supports(new ManageRecurringPaymentsProfileStatusRequest($this->getMock('ArrayAccess')))
        );
    }

    /**
     * @test
     */
    public function shouldSupportManageRecurringPaymentsProfileStatusRequestWithRecurringPaymentDetailsDetailsAsModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $this->assertTrue($action->supports(
            new ManageRecurringPaymentsProfileStatusRequest(new RecurringPaymentDetails))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotManageRecurringPaymentsProfileStatusRequest()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID fields is required.
     */
    public function throwIfProfileIdNotSetInModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatusRequest(array());

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The ACTION fields is required.
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatusRequest(array(
            'PROFILEID' => 'aProfId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiManageRecurringPaymentsProfileStatusMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;

        $action = new ManageRecurringPaymentsProfileStatusAction();
        $action->setApi($apiMock);

        $request = new ManageRecurringPaymentsProfileStatusRequest(array(
            'PROFILEID' => 'theProfileId',
            'ACTION' => 'theAction',
            'NOTE' => 'theNote',
        ));

        $action->execute($request);

        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);

        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('PROFILEID', $fields);
        $this->assertEquals('theProfileId', $fields['PROFILEID']);

        $this->assertArrayHasKey('ACTION', $fields);
        $this->assertEquals('theAction', $fields['ACTION']);

        $this->assertArrayHasKey('NOTE', $fields);
        $this->assertEquals('theNote', $fields['NOTE']);
    }

    /**
     * @test
     */
    public function shouldCallApiManageRecurringPaymentsProfileStatusMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'PROFILEID'=> 'theResponseProfileId',
                )));

                return $response;
            }))
        ;

        $action = new ManageRecurringPaymentsProfileStatusAction();
        $action->setApi($apiMock);

        $request = new ManageRecurringPaymentsProfileStatusRequest(array(
            'PROFILEID' => 'aProfileId',
            'ACTION' => 'anAction',
            'NOTE' => 'aNote',
        ));

        $action->execute($request);

        $this->assertArrayHasKey('PROFILEID', $request->getModel());
        $this->assertEquals('theResponseProfileId', $request->getModel()['PROFILEID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}