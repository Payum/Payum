<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class SetExpressCheckoutActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(SetExpressCheckoutAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(SetExpressCheckoutAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportSetExpressCheckoutRequestAndArrayAccessAsModel(): void
    {
        $action = new SetExpressCheckoutAction();

        $request = new SetExpressCheckout($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotSetExpressCheckoutRequest(): void
    {
        $action = new SetExpressCheckoutAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new SetExpressCheckoutAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfModelNotHavePaymentAmountSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The PAYMENTREQUEST_0_AMT must be set.');
        $action = new SetExpressCheckoutAction();

        $request = new SetExpressCheckout(new ArrayObject());

        $action->execute($request);
    }

    public function testShouldCallApiGetExpressCheckoutDetailsMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $expectedAmount = 154.23;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('setExpressCheckout')
            ->willReturnCallback(function (array $fields) use ($testCase, $expectedAmount) {
                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
                $testCase->assertSame($expectedAmount, $fields['PAYMENTREQUEST_0_AMT']);

                return [];
            })
        ;

        $action = new SetExpressCheckoutAction($apiMock);
        $action->setApi($apiMock);

        $request = new SetExpressCheckout([
            'PAYMENTREQUEST_0_AMT' => $expectedAmount,
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiDoExpressCheckoutMethodAndUpdateInstructionFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('setExpressCheckout')
            ->willReturnCallback(function () {
                return [
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                ];
            })
        ;

        $action = new SetExpressCheckoutAction();
        $action->setApi($apiMock);

        $request = new SetExpressCheckout([
            'PAYMENTREQUEST_0_AMT' => $expectedAmount = 154.23,
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertSame('theFirstname', $model['FIRSTNAME']);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
