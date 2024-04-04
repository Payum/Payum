<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\UpdateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile;

class UpdateRecurringPaymentProfileActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(UpdateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(UpdateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldUpdateRecurringPaymentProfileRequestAndArrayAccessAsModel()
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new UpdateRecurringPaymentProfile($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotUpdateRecurringPaymentProfileRequest()
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfProfileIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILEID fields are required.');
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new UpdateRecurringPaymentProfile(array()));
    }

    public function testShouldCallApiUpdateRecurringPaymentsProfileMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('updateRecurringPaymentsProfile')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertSame('theProfileId', $fields['PROFILEID']);

                return array();
            })
        ;

        $action = new UpdateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new UpdateRecurringPaymentProfile(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiUpdateRecurringPaymentsProfileMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('updateRecurringPaymentsProfile')
            ->willReturnCallback(function () {
                return array(
                    'PROFILEID' => 'theId',
                    'PROFILESTATUS' => 'theStatus',
                );
            })
        ;

        $action = new UpdateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new UpdateRecurringPaymentProfile(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);

        $model = $request->getModel();
        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertSame('theId', $model['PROFILEID']);

        $this->assertArrayHasKey('PROFILESTATUS', $model);
        $this->assertSame('theStatus', $model['PROFILESTATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
