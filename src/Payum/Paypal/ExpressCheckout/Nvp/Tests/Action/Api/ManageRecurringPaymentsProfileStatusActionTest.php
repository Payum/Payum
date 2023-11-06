<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;

class ManageRecurringPaymentsProfileStatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(ManageRecurringPaymentsProfileStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(ManageRecurringPaymentsProfileStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportManageRecurringPaymentsProfileStatusRequestAndArrayAccessAsModel()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $this->assertTrue(
            $action->supports(new ManageRecurringPaymentsProfileStatus($this->createMock('ArrayAccess')))
        );
    }

    public function testShouldNotSupportAnythingNotManageRecurringPaymentsProfileStatusRequest()
    {
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfProfileIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILEID, ACTION fields are required.');
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatus(array());

        $action->execute($request);
    }

    public function testThrowIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The ACTION fields are required.');
        $action = new ManageRecurringPaymentsProfileStatusAction();

        $request = new ManageRecurringPaymentsProfileStatus(array(
            'PROFILEID' => 'aProfId',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiManageRecurringPaymentsProfileStatusMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertSame('theProfileId', $fields['PROFILEID']);

                $testCase->assertArrayHasKey('ACTION', $fields);
                $testCase->assertSame('theAction', $fields['ACTION']);

                $testCase->assertArrayHasKey('NOTE', $fields);
                $testCase->assertSame('theNote', $fields['NOTE']);

                return array();
            })
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

    public function testShouldCallApiManageRecurringPaymentsProfileStatusMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->willReturnCallback(function () {
                return array(
                    'PROFILEID' => 'theResponseProfileId',
                );
            })
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
        $this->assertSame('theResponseProfileId', $model['PROFILEID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
