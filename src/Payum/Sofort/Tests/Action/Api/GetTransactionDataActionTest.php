<?php

namespace Invit\PayumSofortueberweisung\Tests\Action\Api;

use Invit\PayumSofortueberweisung\Action\Api\GetTransactionDataAction;
use Invit\PayumSofortueberweisung\Request\Api\GetTransactionData;
use Invit\PayumSofortueberweisung\Action\Api\BaseApiAwareAction;
use Invit\PayumSofortueberweisung\Api;

class GetTransactionDataActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArgument()
    {
        new GetTransactionDataAction();
    }

    /**
     * @test
     */
    public function shouldSupportGetTransactionDataRequestWithArrayAccessAsModel()
    {
        $action = new GetTransactionDataAction();

        $this->assertTrue($action->supports(new GetTransactionData($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingGetTransactionDataRequest()
    {
        $action = new GetTransactionDataAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetTransactionDataAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The parameter "transaction_id" must be set. Have you run CreateTransactionAction?
     */
    public function throwIfTransactionIdParameterIsNotSet()
    {
        $action = new GetTransactionDataAction();

        $request = new GetTransactionData(array());
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
