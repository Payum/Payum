<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\StopRecurringPayment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class StopRecurringPaymentActionTest extends TestCase
{
    protected $requiredFields = [
        'agreementRef' => 'aRef',
    ];

    public function provideRequiredFields()
    {
        $fields = [];

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(StopRecurringPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(StopRecurringPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotRecurringApiAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new StopRecurringPaymentAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportStopRecurringPaymentRequestWithArrayAccessAsModel(): void
    {
        $action = new StopRecurringPaymentAction();

        $this->assertTrue($action->supports(new StopRecurringPayment($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotStopRecurringPaymentRequest(): void
    {
        $action = new StopRecurringPaymentAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportStopRecurringPaymentRequestWithNotArrayAccessModel(): void
    {
        $action = new StopRecurringPaymentAction();

        $this->assertFalse($action->supports(new StopRecurringPayment(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new StopRecurringPaymentAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField): void
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new StopRecurringPaymentAction();

        $action->execute(new StopRecurringPayment($this->requiredFields));
    }

    public function testShouldStopRecurringPayment(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('stop')
            ->with($this->requiredFields)
            ->willReturn([
                'errorCode' => 'theCode',
            ]);

        $action = new StopRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new StopRecurringPayment($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theCode', $model['errorCode']);
    }

    /**
     * @return MockObject|RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock(RecurringApi::class, [], [], '', false);
    }
}
