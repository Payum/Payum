<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\CheckRecurringPaymentAction;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\CheckRecurringPayment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CheckRecurringPaymentActionTest extends TestCase
{
    /**
     * @var array{agreementRef: string}
     */
    protected array $requiredFields = [
        'agreementRef' => 'aRef',
    ];

    /**
     * @return array<int, mixed[]>
     */
    public function provideRequiredFields(): array
    {
        $fields = [];

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CheckRecurringPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CheckRecurringPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotRecurringApiAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new CheckRecurringPaymentAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCheckRecurringPaymentRequestWithArrayAccessAsModel(): void
    {
        $action = new CheckRecurringPaymentAction();

        $this->assertTrue($action->supports(new CheckRecurringPayment($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCheckRecurringPaymentRequest(): void
    {
        $action = new CheckRecurringPaymentAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCheckRecurringPaymentRequestWithNotArrayAccessModel(): void
    {
        $action = new CheckRecurringPaymentAction();

        $this->assertFalse($action->supports(new CheckRecurringPayment(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CheckRecurringPaymentAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField): void
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CheckRecurringPaymentAction();

        $action->execute(new CheckRecurringPayment($this->requiredFields));
    }

    public function testShouldCheckRecurringPayment(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredFields)
            ->willReturn([
                'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT,
            ]);

        $action = new CheckRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new CheckRecurringPayment($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame(RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT, $model['recurringStatus']);
    }

    /**
     * @return MockObject|RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock(RecurringApi::class);
    }
}
