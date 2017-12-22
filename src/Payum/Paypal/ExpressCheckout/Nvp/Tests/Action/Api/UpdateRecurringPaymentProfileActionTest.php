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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new UpdateRecurringPaymentProfileAction();
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID fields are required.
     */
    public function throwIfProfileIdNotSetInModel()
    {
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
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertEquals('theProfileId', $fields['PROFILEID']);

                return array();
            }))
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
            ->will($this->returnCallback(function () {
                return array(
                    'PROFILEID' => 'theId',
                    'PROFILESTATUS' => 'theStatus',
                );
            }))
        ;

        $action = new UpdateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new UpdateRecurringPaymentProfile(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);

        $model = $request->getModel();
        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertEquals('theId', $model['PROFILEID']);

        $this->assertArrayHasKey('PROFILESTATUS', $model);
        $this->assertEquals('theStatus', $model['PROFILESTATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
