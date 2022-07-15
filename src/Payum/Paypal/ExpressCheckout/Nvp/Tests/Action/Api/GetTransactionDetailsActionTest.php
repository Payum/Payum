<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GetTransactionDetailsActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetTransactionDetailsRequestAndArrayAccessAsModel(): void
    {
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails($this->createMock(ArrayAccess::class), $paymentRequestN = 5);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotGetTransactionDetailsRequest(): void
    {
        $action = new GetTransactionDetailsAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new GetTransactionDetailsAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfZeroPaymentRequestTransactionIdNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PAYMENTREQUEST_5_TRANSACTIONID must be set.');
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails([], $paymentRequestN = 5);

        $action->execute($request);
    }

    public function testShouldCallApiGetTransactionDetailsMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertSame('theTransactionId', $fields['TRANSACTIONID']);

                return [];
            })
        ;

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails([
            'PAYMENTREQUEST_5_TRANSACTIONID' => 'theTransactionId',
        ], $paymentRequestN = 5);

        $action->execute($request);
    }

    public function testShouldCallApiGetTransactionDetailsAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->willReturnCallback(fn () => [
                'PAYMENTSTATUS' => 'theStatus',
            ])
        ;

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails([
            'PAYMENTREQUEST_5_TRANSACTIONID' => 'aTransactionId',
        ], $paymentRequestN = 5);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PAYMENTREQUEST_5_PAYMENTSTATUS', $model);
        $this->assertSame('theStatus', $model['PAYMENTREQUEST_5_PAYMENTSTATUS']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
