<?php

namespace Invit\PayumSofortueberweisung\Tests\Action\Api;

use Invit\PayumSofortueberweisung\Request\Api\CreateTransaction;
use Invit\PayumSofortueberweisung\Action\Api\CreateTransactionAction;
use Invit\PayumSofortueberweisung\Action\Api\BaseApiAwareAction;
use Invit\PayumSofortueberweisung\Api;

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
     * @expectedExceptionMessage The parameter "Amount" must be set.
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
     * @expectedExceptionMessage The parameter "currency_code" must be set.
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
     * @expectedExceptionMessage The parameter "reason" must be set.
     */
    public function throwIfReasonParameterIsNotSet()
    {
        $action = new CreateTransactionAction();

        $request = new CreateTransaction(array('amount' => 55, 'currency_code' => 'CHF'));
        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Invit\PayumSofortueberweisung\Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, array(), array(), '', false);
    }
}
