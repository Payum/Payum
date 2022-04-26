<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Request\Api\CreateTransaction;
use Payum\Sofort\Action\Api\CreateTransactionAction;
use Payum\Sofort\Api;

class CreateTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportCreateTransactionRequestWithArrayAccessAsModel()
    {
        $action = new CreateTransactionAction();

        $this->assertTrue($action->supports(new CreateTransaction($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingCreateTransactionRequest()
    {
        $action = new CreateTransactionAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfAmountParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The amount, currency_code, reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array());
        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfCurrencyCodeParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The currency_code, reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array('amount' => 55));
        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfReasonParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The reason, success_url, notification_url fields are required.');
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array('amount' => 55, 'currency_code' => 'CHF'));
        $action->execute($request);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Sofort\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, array(), array(), '', false);
    }
}
