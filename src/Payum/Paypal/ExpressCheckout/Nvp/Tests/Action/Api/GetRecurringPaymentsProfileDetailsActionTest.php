<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;

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
            $action->supports(new GetRecurringPaymentsProfileDetails($this->getMock('ArrayAccess')))
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
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID fields are required.
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $request = new GetRecurringPaymentsProfileDetails(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetRecurringPaymentsProfileDetailsMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertEquals('theProfileId', $fields['PROFILEID']);

                return array();
            }))
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetails(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);
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
            ->will($this->returnCallback(function () {
                return array(
                    'STATUS' => 'theStatus',
                );
            }))
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetails(array(
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
