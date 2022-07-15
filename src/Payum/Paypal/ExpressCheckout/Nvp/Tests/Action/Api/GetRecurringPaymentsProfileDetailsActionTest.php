<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GetRecurringPaymentsProfileDetailsActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(GetRecurringPaymentsProfileDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(GetRecurringPaymentsProfileDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetRecurringPaymentsProfileDetailsRequestAndArrayAccessAsModel(): void
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $this->assertTrue(
            $action->supports(new GetRecurringPaymentsProfileDetails($this->createMock(ArrayAccess::class)))
        );
    }

    public function testShouldNotSupportAnythingNotGetRecurringPaymentsProfileDetailsRequest(): void
    {
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfTokenNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The PROFILEID fields are required.');
        $action = new GetRecurringPaymentsProfileDetailsAction();

        $request = new GetRecurringPaymentsProfileDetails([]);

        $action->execute($request);
    }

    public function testShouldCallApiGetRecurringPaymentsProfileDetailsMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertSame('theProfileId', $fields['PROFILEID']);

                return [];
            })
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetails([
            'PROFILEID' => 'theProfileId',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiGetRecurringPaymentsProfileDetailsMethodAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getRecurringPaymentsProfileDetails')
            ->willReturnCallback(fn () => [
                'STATUS' => 'theStatus',
            ])
        ;

        $action = new GetRecurringPaymentsProfileDetailsAction();
        $action->setApi($apiMock);

        $request = new GetRecurringPaymentsProfileDetails([
            'PROFILEID' => 'theProfileId',
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('STATUS', $model);
        $this->assertSame('theStatus', $model['STATUS']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
