<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;

class AutoPayPaymentDetailsStatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AutoPayPaymentDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetBinaryStatus(array(
            'autoPay' => true,
        ))));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(array())));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(array(
            'autoPay' => false,
        ))));
    }

    public function testShouldNotSupportAnythingNotBinaryMaskStatusRequest()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportBinaryMaskStatusRequestWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new AutoPayPaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkNewIfTransactionStatusNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedIfPurchaseOperationAuthorizeAndTransactionStatusThree()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkCapturedIfPurchaseOperationSaleAndTransactionStatusZero()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedIfTransactionStatusNeitherZeroOrThree()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $status = new GetBinaryStatus(array(
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'foobarbaz',
            'autoPay' => true,
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
