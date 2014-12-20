<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;

class ManageRecurringPaymentsProfileStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
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
            $action->supports(new ManageRecurringPaymentsProfileStatus($this->getMock('ArrayAccess')))
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
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID, ACTION fields are required.
     */
    public function throwIfProfileIdNotSetInModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatus(array());

        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The ACTION fields are required.
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatus(array(
            'PROFILEID' => 'aProfId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiManageRecurringPaymentsProfileStatusMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertEquals('theProfileId', $fields['PROFILEID']);

                $testCase->assertArrayHasKey('ACTION', $fields);
                $testCase->assertEquals('theAction', $fields['ACTION']);

                $testCase->assertArrayHasKey('NOTE', $fields);
                $testCase->assertEquals('theNote', $fields['NOTE']);

                return array();
            }))
        ;

        $action = new ManageRecurringPaymentsProfileStatusAction();
        $action->setApi($apiMock);

        $request = new ManageRecurringPaymentsProfileStatus(array(
            'PROFILEID' => 'theProfileId',
            'ACTION' => 'theAction',
            'NOTE' => 'theNote',
        ));

        $action->execute($request);
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
            ->will($this->returnCallback(function () {
                return array(
                    'PROFILEID' => 'theResponseProfileId',
                );
            }))
        ;

        $action = new ManageRecurringPaymentsProfileStatusAction();
        $action->setApi($apiMock);

        $request = new ManageRecurringPaymentsProfileStatus(array(
            'PROFILEID' => 'aProfileId',
            'ACTION' => 'anAction',
            'NOTE' => 'aNote',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertEquals('theResponseProfileId', $model['PROFILEID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
