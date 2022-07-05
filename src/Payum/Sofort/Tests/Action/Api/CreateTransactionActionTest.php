<?php

namespace Payum\Sofort\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Sofort\Action\Api\CreateTransactionAction;
use Payum\Sofort\Api;
use Payum\Sofort\Request\Api\CreateTransaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateTransactionActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testShouldSupportCreateTransactionRequestWithArrayAccessAsModel()
    {
        $action = new CreateTransactionAction();

        $this->assertTrue($action->supports(new CreateTransaction($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingCreateTransactionRequest()
    {
        $action = new CreateTransactionAction($this->createApiMock());

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreateTransactionAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    public function testThrowIfAmountParameterIsNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The amount, currency_code, reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction([]);
        $action->execute($request);
    }

    public function testThrowIfCurrencyCodeParameterIsNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The currency_code, reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction([
            'amount' => 55,
        ]);
        $action->execute($request);
    }

    public function testThrowIfReasonParameterIsNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction([
            'amount' => 55,
            'currency_code' => 'CHF',
        ]);
        $action->execute($request);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
