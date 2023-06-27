<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DoReferenceTransactionActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(DoReferenceTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(DoReferenceTransactionAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportDoReferenceTransactionRequestAndArrayAccessAsModel(): void
    {
        $action = new DoReferenceTransactionAction();

        $this->assertTrue($action->supports(new DoReferenceTransaction($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotDoReferenceTransactionRequest(): void
    {
        $action = new DoReferenceTransactionAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new DoReferenceTransactionAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfReferenceIdNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('REFERENCEID must be set.');
        $action = new DoReferenceTransactionAction();

        $action->execute(new DoReferenceTransaction([]));
    }

    public function testThrowIfPaymentActionNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PAYMENTACTION must be set.');
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction([
            'REFERENCEID' => 'aReferenceId',
        ]);

        $action->execute($request);
    }

    public function testThrowIfAmtNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('AMT must be set.');
        $action = new DoReferenceTransactionAction();

        $request = new DoReferenceTransaction([
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiDoReferenceTransactionMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->willReturnCallback(function (array $fields) use ($testCase): array {
                $testCase->assertArrayHasKey('REFERENCEID', $fields);
                $testCase->assertSame('theReferenceId', $fields['REFERENCEID']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertSame('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('PAYMENTACTION', $fields);
                $testCase->assertSame('theAction', $fields['PAYMENTACTION']);

                return [];
            })
        ;

        $action = new DoReferenceTransactionAction();
        $action->setApi($apiMock);

        $request = new DoReferenceTransaction([
            'REFERENCEID' => 'theReferenceId',
            'PAYMENTACTION' => 'theAction',
            'AMT' => 'theAmt',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiDoReferenceTransactionMethodAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doReferenceTransaction')
            ->willReturnCallback(fn () => [
                'FIRSTNAME' => 'theFirstname',
                'EMAIL' => 'the@example.com',
            ])
        ;

        $action = new DoReferenceTransactionAction();
        $action->setApi($apiMock);

        $request = new DoReferenceTransaction([
            'REFERENCEID' => 'aReferenceId',
            'PAYMENTACTION' => 'anAction',
            'AMT' => 'anAmt',
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertSame('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
