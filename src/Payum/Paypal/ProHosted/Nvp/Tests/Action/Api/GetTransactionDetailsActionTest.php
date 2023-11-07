<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ProHosted\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GetTransactionDetailsActionTest extends TestCase
{
    public function testShouldImplementsApiAwareAction()
    {
        $rc = new ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetTransactionDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotGetTransactionDetailsRequest()
    {
        $action = new GetTransactionDetailsAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new GetTransactionDetailsAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfZeroPaymentRequestTransactionIdNotSetInModel()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('TRANSACTIONID must be set.');
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails([]);

        $action->execute($request);
    }

    public function testShouldCallApiGetTransactionDetailsAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->willReturnCallback(fn () => [
                'PAYMENTSTATUS' => 'theStatus',
            ]);

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails([
            'txn_id' => 'aTransactionId',
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PAYMENTSTATUS', $model);
        $this->assertSame('theStatus', $model['PAYMENTSTATUS']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
