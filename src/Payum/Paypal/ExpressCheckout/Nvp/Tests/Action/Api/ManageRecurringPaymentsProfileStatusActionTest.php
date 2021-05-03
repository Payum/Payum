<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;

class ManageRecurringPaymentsProfileStatusActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(ManageRecurringPaymentsProfileStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(ManageRecurringPaymentsProfileStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportManageRecurringPaymentsProfileStatusRequestAndArrayAccessAsModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $this->assertTrue(
            $action->supports(new ManageRecurringPaymentsProfileStatus($this->createMock('ArrayAccess')))
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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfProfileIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILEID, ACTION fields are required.');
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatus(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The ACTION fields are required.');
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
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
