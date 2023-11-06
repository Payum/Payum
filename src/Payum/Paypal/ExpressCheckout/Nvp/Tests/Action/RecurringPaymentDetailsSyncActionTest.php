<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

class RecurringPaymentDetailsSyncActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(RecurringPaymentDetailsSyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncAndArrayAsModelWhichHasBillingPeriodSet()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $paymentDetails = array(
            'BILLINGPERIOD' => 12,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotSyncRequest()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new RecurringPaymentDetailsSyncAction();

        $action->execute(new \stdClass());
    }

    public function testShouldDoNothingIfProfileIdNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new RecurringPaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync(array(
            'BILLINGPERIOD' => 12,
        ));

        $action->execute($request);
    }

    public function testShouldRequestGetRecurringPaymentsProfileDetailsActionIfProfileIdSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails'))
        ;

        $action = new RecurringPaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync(array(
            'BILLINGPERIOD' => 'aBillingPeriod',
            'PROFILEID' => 'anId',
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
