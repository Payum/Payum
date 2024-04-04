<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Action\Api\GetTransactionDataAction;
use Payum\Sofort\Request\Api\GetTransactionData;
use Payum\Sofort\Api;

class GetTransactionDataActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testShouldSupportGetTransactionDataRequestWithArrayAccessAsModel()
    {
        $action = new GetTransactionDataAction();

        $this->assertTrue($action->supports(new GetTransactionData($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingGetTransactionDataRequest()
    {
        $action = new GetTransactionDataAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new GetTransactionDataAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    public function testThrowIfTransactionIdParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The parameter "transaction_id" must be set. Have you run CreateTransactionAction?');
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
