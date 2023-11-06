<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class CancelActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CancelAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CancelAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    public function testShouldSupportEmptyModel()
    {
        $action = new CancelAction();

        $request = new Cancel([]);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportCancelRequestWithArrayAsModelWhichHasPendingReasonAsAuthorized()
    {
        $action = new CancelAction();

        $payment = array(
           'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        );

        $request = new Cancel($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportCancelRequestWithArrayAsModelWhichHasPendingReasonAsOtherThanAuthorized()
    {
        $action = new CancelAction();

        $payment = array(
           'PAYMENTINFO_0_PENDINGREASON' => 'Foo',
        );

        $request = new Cancel($payment);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportModelWithBillingPeriod()
    {
        $action = new CancelAction();

        $payment = array(
           'BILLINGPERIOD' => 'Month',
        );

        $request = new Cancel($payment);

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportCancelRequestWithNoArrayAccessAsModel()
    {
        $action = new CancelAction();

        $request = new Cancel(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotCancelRequest()
    {
        $action = new CancelAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CancelAction();

        $action->execute(new \stdClass());
    }

    public function testShouldNotExecuteDoVoidIfTransactionIdNotSet()
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

    public function testShouldExecuteDoVoidIfTransactionIdSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                array($this->isInstanceOf(DoVoid::class)),
                array($this->isInstanceOf(Sync::class))
            )
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'TRANSACTIONID' => 'theId',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
