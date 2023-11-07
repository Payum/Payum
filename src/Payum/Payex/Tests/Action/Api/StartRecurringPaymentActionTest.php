<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\StartRecurringPaymentAction;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\StartRecurringPayment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class StartRecurringPaymentActionTest extends TestCase
{
    protected $requiredFields = [
        'agreementRef' => 'aRef',
        'startDate' => '2013-10-10 12:21:21',
        'periodType' => RecurringApi::PERIODTYPE_HOURS,
        'period' => 2,
        'alertPeriod' => 0,
        'price' => 1000,
        'productNumber' => 'aProductNumber',
        'orderId' => 'anOrderId',
        'description' => 'aDesc',
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
        $rc = new ReflectionClass(StartRecurringPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(StartRecurringPaymentAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotRecurringApiAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new StartRecurringPaymentAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportStartRecurringPaymentRequestWithArrayAccessAsModel(): void
    {
        $action = new StartRecurringPaymentAction();

        $this->assertTrue($action->supports(new StartRecurringPayment($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotStartRecurringPaymentRequest(): void
    {
        $action = new StartRecurringPaymentAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportStartRecurringPaymentRequestWithNotArrayAccessModel(): void
    {
        $action = new StartRecurringPaymentAction();

        $this->assertFalse($action->supports(new StartRecurringPayment(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new StartRecurringPaymentAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField): void
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new StartRecurringPaymentAction();

        $action->execute(new StartRecurringPayment($this->requiredFields));
    }

    public function testShouldStartRecurringPayment(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('start')
            ->with($this->requiredFields)
            ->willReturn([
                'recurringRef' => 'theRecRef',
            ]);

        $action = new StartRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new StartRecurringPayment($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theRecRef', $model['recurringRef']);
    }

    /**
     * @return MockObject|RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock(RecurringApi::class);
    }
}
