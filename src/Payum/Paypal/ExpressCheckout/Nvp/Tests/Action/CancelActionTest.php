<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CancelActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CancelAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(CancelAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    public function testShouldSupportEmptyModel(): void
    {
        $action = new CancelAction();

        $request = new Cancel([]);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportCancelRequestWithArrayAsModelWhichHasPendingReasonAsAuthorized(): void
    {
        $action = new CancelAction();

        $payment = [
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ];

        $request = new Cancel($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportCancelRequestWithArrayAsModelWhichHasPendingReasonAsOtherThanAuthorized(): void
    {
        $action = new CancelAction();

        $payment = [
            'PAYMENTINFO_0_PENDINGREASON' => 'Foo',
        ];

        $request = new Cancel($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportModelWithBillingPeriod(): void
    {
        $action = new CancelAction();

        $payment = [
            'BILLINGPERIOD' => 'Month',
        ];

        $request = new Cancel($payment);

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportCancelRequestWithNoArrayAccessAsModel(): void
    {
        $action = new CancelAction();

        $request = new Cancel(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotCancelRequest(): void
    {
        $action = new CancelAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CancelAction();

        $action->execute(new stdClass());
    }

    public function testShouldNotExecuteDoVoidIfTransactionIdNotSet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel([]);

        $action->execute($request);
    }

    public function testShouldExecuteDoVoidIfTransactionIdSet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(DoVoid::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel([
            'TRANSACTIONID' => 'theId',
        ]);

        $action->execute($request);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
