<?php

namespace Payum\Sofort\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Sofort\Action\Api\GetTransactionDataAction;
use Payum\Sofort\Api;
use Payum\Sofort\Request\Api\GetTransactionData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GetTransactionDataActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(GetTransactionDataAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testShouldSupportGetTransactionDataRequestWithArrayAccessAsModel()
    {
        $action = new GetTransactionDataAction();

        $this->assertTrue($action->supports(new GetTransactionData($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingGetTransactionDataRequest()
    {
        $action = new GetTransactionDataAction($this->createApiMock());

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new GetTransactionDataAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    public function testThrowIfTransactionIdParameterIsNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The parameter "transaction_id" must be set. Have you run CreateTransactionAction?');
        $action = new GetTransactionDataAction();

        $request = new GetTransactionData([]);
        $action->execute($request);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
