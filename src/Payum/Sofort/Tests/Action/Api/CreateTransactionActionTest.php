<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Sofort\Request\Api\CreateTransaction;
use Payum\Sofort\Action\Api\CreateTransactionAction;
use Payum\Sofort\Action\Api\BaseApiAwareAction;
use Payum\Sofort\Api;

class CreateTransactionActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
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

        $this->assertTrue($action->supports(new CreateTransaction($this->getMock('ArrayAccess'))));
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
        return $this->getMock(Api::class, array(), array(), '', false);
    }
}
