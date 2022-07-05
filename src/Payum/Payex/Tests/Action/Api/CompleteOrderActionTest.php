<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\CompleteOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CompleteOrderActionTest extends TestCase
{
    protected $requiredFields = [
        'orderRef' => 'aRef',
    ];

    public function provideRequiredFields()
    {
        $fields = [];

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(CompleteOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(CompleteOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new CompleteOrderAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCompleteOrderRequestWithArrayAccessAsModel()
    {
        $action = new CompleteOrderAction();

        $this->assertTrue($action->supports(new CompleteOrder($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCompleteOrderRequest()
    {
        $action = new CompleteOrderAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCompleteOrderRequestWithNotArrayAccessModel()
    {
        $action = new CompleteOrderAction();

        $this->assertFalse($action->supports(new CompleteOrder(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CompleteOrderAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CompleteOrderAction();

        $action->execute(new CompleteOrder($this->requiredFields));
    }

    public function testShouldCompletePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('complete')
            ->with($this->requiredFields)
            ->willReturn([
                'transactionRef' => 'theRef',
            ]);

        $action = new CompleteOrderAction();
        $action->setApi($apiMock);

        $request = new CompleteOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theRef', $model['transactionRef']);
    }

    /**
     * @return MockObject|OrderApi
     */
    protected function createApiMock()
    {
        return $this->createMock(OrderApi::class, [], [], '', false);
    }
}
