<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DoExpressCheckoutPaymentActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(DoExpressCheckoutPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(DoExpressCheckoutPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportDoExpressCheckoutPaymentRequestAndArrayAccessAsModel(): void
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertTrue($action->supports(new DoExpressCheckoutPayment($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotDoExpressCheckoutPaymentRequest(): void
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new DoExpressCheckoutPaymentAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfTokenNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        $action = new DoExpressCheckoutPaymentAction();

        $action->execute(new DoExpressCheckoutPayment([]));
    }

    public function testThrowIfPayerIdNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PAYERID must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment([
            'TOKEN' => 'aToken',
        ]);

        $action->execute($request);
    }

    public function testThrowIfZeroPaymentRequestActionNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PAYMENTREQUEST_0_PAYMENTACTION must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ]);

        $action->execute($request);
    }

    public function testThrowIfZeroPaymentRequestAmtNotSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PAYMENTREQUEST_0_AMT must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'anAction',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiDoExpressCheckoutMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertSame('theToken', $fields['TOKEN']);

                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
                $testCase->assertSame('theAmt', $fields['PAYMENTREQUEST_0_AMT']);

                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $fields);
                $testCase->assertSame('theAction', $fields['PAYMENTREQUEST_0_PAYMENTACTION']);

                $testCase->assertArrayHasKey('PAYERID', $fields);
                $testCase->assertSame('thePayerId', $fields['PAYERID']);

                return [];
            })
        ;

        $action = new DoExpressCheckoutPaymentAction();
        $action->setApi($apiMock);

        $request = new DoExpressCheckoutPayment([
            'TOKEN' => 'theToken',
            'PAYERID' => 'thePayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'theAction',
            'PAYMENTREQUEST_0_AMT' => 'theAmt',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiDoExpressCheckoutMethodAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->willReturnCallback(fn () => [
                'FIRSTNAME' => 'theFirstname',
                'EMAIL' => 'the@example.com',
            ])
        ;

        $action = new DoExpressCheckoutPaymentAction();
        $action->setApi($apiMock);

        $request = new DoExpressCheckoutPayment([
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'anAction',
            'PAYMENTREQUEST_0_AMT' => 'anAmt',
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
