<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Action\Api\GetTransactionDataAction;
use Payum\Sofort\Request\Api\GetTransactionData;
use Payum\Sofort\Api;

class GetTransactionDataActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
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

        $this->assertTrue($action->supports(new GetTransactionData($this->createMock('ArrayAccess'))));
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Sofort\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, array(), array(), '', false);
    }
}
