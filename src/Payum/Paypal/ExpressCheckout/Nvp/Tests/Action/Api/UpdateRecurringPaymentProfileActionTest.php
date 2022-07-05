<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\UpdateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile;

class UpdateRecurringPaymentProfileActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(UpdateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(UpdateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldUpdateRecurringPaymentProfileRequestAndArrayAccessAsModel()
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new UpdateRecurringPaymentProfile($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotUpdateRecurringPaymentProfileRequest()
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfProfileIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILEID fields are required.');
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new UpdateRecurringPaymentProfile(array()));
    }

    /**
     * @test
     */
    public function shouldCallApiUpdateRecurringPaymentsProfileMethodWithExpectedRequiredArguments()
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

    /**
     * @test
     */
    public function shouldCallApiUpdateRecurringPaymentsProfileMethodAndUpdateModelFromResponseOnSuccess()
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
