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
    public function couldBeConstructedWithoutAnyArgument()
    {
        new CreateTransactionAction();
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CreateTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The amount, currency_code, reason, success_url, notification_url fields are required.
     */
    public function throwIfAmountParameterIsNotSet()
    {
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array());
        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The currency_code, reason, success_url, notification_url fields are required.
     */
    public function throwIfCurrencyCodeParameterIsNotSet()
    {
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array('amount' => 55));
        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The reason, success_url, notification_url fields are required.
     */
    public function throwIfReasonParameterIsNotSet()
    {
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array('amount' => 55, 'currency_code' => 'CHF'));
        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Sofort\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, array(), array(), '', false);
    }
}
