<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\CheckOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CheckOrderActionTest extends TestCase
{
    protected $requiredFields = [
        'transactionNumber' => 'aNum',
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
        $rc = new ReflectionClass(CheckOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(CheckOrderAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new CheckOrderAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCheckOrderRequestWithArrayAccessAsModel()
    {
        $action = new CheckOrderAction();

        $this->assertTrue($action->supports(new CheckOrder($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCheckOrderRequest()
    {
        $action = new CheckOrderAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCheckOrderRequestWithNotArrayAccessModel()
    {
        $action = new CheckOrderAction();

        $this->assertFalse($action->supports(new CheckOrder(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CheckOrderAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CheckOrderAction();

        $action->execute(new CheckOrder($this->requiredFields));
    }

    public function testShouldCompletePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredFields)
            ->willReturn([
                'transactionStatus' => 'theStatus',
            ]);

        $action = new CheckOrderAction();
        $action->setApi($apiMock);

        $request = new CheckOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theStatus', $model['transactionStatus']);
    }

    /**
     * @return MockObject|OrderApi
     */
    protected function createApiMock()
    {
        return $this->createMock(OrderApi::class, [], [], '', false);
    }
}
