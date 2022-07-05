<?php

namespace Payum\Payex\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AutoPayPaymentDetailsStatusActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(AutoPayPaymentDetailsStatusAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetBinaryStatus([
            'autoPay' => true,
        ])));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus([])));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus([
            'autoPay' => true,
            'recurring' => true,
        ])));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus([
            'autoPay' => false,
        ])));
    }

    public function testShouldNotSupportAnythingNotBinaryMaskStatusRequest()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new AutoPayPaymentDetailsStatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkNewIfTransactionStatusNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus([
            'autoPay' => true,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfPurchaseOperationAuthorizeAndTransactionStatusThree()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'autoPay' => true,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkCapturedIfPurchaseOperationSaleAndTransactionStatusZero()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'autoPay' => true,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedIfTransactionStatusNeitherZeroOrThree()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'foobarbaz',
            'autoPay' => true,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
