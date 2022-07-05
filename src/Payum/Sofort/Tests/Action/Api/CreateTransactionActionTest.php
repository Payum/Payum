<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Action\Api\CreateTransactionAction;
use Payum\Sofort\Api;
use Payum\Sofort\Request\Api\CreateTransaction;

class CreateTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testShouldSupportCreateTransactionRequestWithArrayAccessAsModel()
    {
        $action = new CreateTransactionAction();

        $this->assertTrue($action->supports(new CreateTransaction($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingCreateTransactionRequest()
    {
        $action = new CreateTransactionAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    public function testThrowIfAmountParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The amount, currency_code, reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction([]);
        $action->execute($request);
    }

    public function testThrowIfCurrencyCodeParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The currency_code, reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(['amount' => 55]);
        $action->execute($request);
    }

    public function testThrowIfReasonParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(['amount' => 55, 'currency_code' => 'CHF']);
        $action->execute($request);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Sofort\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
