<?php
namespace Payum\Payex\Tests\Action;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;

class AutoPayPaymentDetailsStatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\AutoPayPaymentDetailsStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AutoPayPaymentDetailsStatusAction();
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToTrue()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetBinaryStatus(array(
            'autoPay' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPayNotSet()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToTrueAndRecurringSetToTrue()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(array(
            'autoPay' => true,
            'recurring' => true,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(array(
            'autoPay' => false,
        ))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotBinaryMaskStatusRequest()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportBinaryMaskStatusRequestWithNotArrayAccessModel()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetBinaryStatus(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new AutoPayPaymentDetailsStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfTransactionStatusNotSet()
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

    /**
     * @test
     */
    public function shouldMarkCapturedIfPurchaseOperationAuthorizeAndTransactionStatusThree()
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

    /**
     * @test
     */
    public function shouldMarkCapturedIfPurchaseOperationSaleAndTransactionStatusZero()
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

    /**
     * @test
     */
    public function shouldMarkFailedIfTransactionStatusNeitherZeroOrThree()
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
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
