<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\UpdateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class UpdateRecurringPaymentProfileActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(UpdateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(UpdateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldUpdateRecurringPaymentProfileRequestAndArrayAccessAsModel(): void
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new UpdateRecurringPaymentProfile($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotUpdateRecurringPaymentProfileRequest(): void
    {
        $action = new UpdateRecurringPaymentProfileAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfProfileIdNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The PROFILEID fields are required.');
        $action = new UpdateRecurringPaymentProfileAction();

        $action->execute(new UpdateRecurringPaymentProfile([]));
    }

    public function testShouldCallApiUpdateRecurringPaymentsProfileMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('updateRecurringPaymentsProfile')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertSame('theProfileId', $fields['PROFILEID']);

                return [];
            })
        ;

        $action = new UpdateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new UpdateRecurringPaymentProfile([
            'PROFILEID' => 'theProfileId',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiUpdateRecurringPaymentsProfileMethodAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('updateRecurringPaymentsProfile')
            ->willReturnCallback(function () {
                return [
                    'PROFILEID' => 'theId',
                    'PROFILESTATUS' => 'theStatus',
                ];
            })
        ;

        $action = new UpdateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new UpdateRecurringPaymentProfile([
            'PROFILEID' => 'theProfileId',
        ]);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertSame('theId', $model['PROFILEID']);

        $this->assertArrayHasKey('PROFILESTATUS', $model);
        $this->assertSame('theStatus', $model['PROFILESTATUS']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
