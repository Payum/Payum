<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;

class GetRecurringPaymentsProfileDetailsActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetRecurringPaymentsProfileDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(GetRecurringPaymentsProfileDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetRecurringPaymentsProfileDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $this->assertTrue(
            $action->supports(new GetRecurringPaymentsProfileDetails($this->createMock('ArrayAccess')))
        );
    }

    public function testShouldNotSupportAnythingNotGetRecurringPaymentsProfileDetailsRequest()
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILEID fields are required.');
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $request = new GetRecurringPaymentsProfileDetails(array());

        $action->execute($request);
    }

    public function testShouldCallApiGetRecurringPaymentsProfileDetailsMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertSame('theProfileId', $fields['PROFILEID']);

                return array();
            })
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetails(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiGetRecurringPaymentsProfileDetailsMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->willReturnCallback(function () {
                return array(
                    'STATUS' => 'theStatus',
                );
            })
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetails(array(
            'PROFILEID' => 'theProfileId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('STATUS', $model);
        $this->assertSame('theStatus', $model['STATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
