<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\RefundTransaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RefundTransactionActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldUseApiAwareTrait(): void
    {
        $rc = new ReflectionClass(RefundTransactionAction::class);

        $this->assertContains(ApiAwareTrait::class, $rc->getTraitNames());
    }

    public function testShouldSupportRefundTransactionRequestAndArrayAccessAsModel(): void
    {
        $action = new RefundTransactionAction();

        $this->assertTrue(
            $action->supports(new RefundTransaction($this->createMock(ArrayAccess::class)))
        );
    }

    public function testShouldNotSupportAnythingNotRefundTransactionRequest(): void
    {
        $action = new RefundTransactionAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new RefundTransactionAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfAuthorizationIdNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The TRANSACTIONID fields are required.');

        $action = new RefundTransactionAction();

        $request = new RefundTransaction([]);

        $action->execute($request);
    }

    public function testShouldCallApiRefundTransactionMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('RefundTransaction')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertSame('theOriginalTransactionId', $fields['TRANSACTIONID']);

                return [];
            })
        ;

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction([
            'TRANSACTIONID' => 'theOriginalTransactionId',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiRefundTransactionMethodAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('RefundTransaction')
            ->willReturnCallback(fn () => [
                'TRANSACTIONID' => 'theTransactionId',
                'REFUNDTRANSACTIONID' => 'theRefundTransactionId',
            ])
        ;

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction([
            'TRANSACTIONID' => 'theTransactionId',
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('TRANSACTIONID', $model);
        $this->assertSame('theTransactionId', $model['TRANSACTIONID']);

        $this->assertArrayHasKey('REFUNDTRANSACTIONID', $model);
        $this->assertSame('theRefundTransactionId', $model['REFUNDTRANSACTIONID']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
