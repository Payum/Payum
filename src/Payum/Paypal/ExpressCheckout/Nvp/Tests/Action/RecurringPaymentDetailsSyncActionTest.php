<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RecurringPaymentDetailsSyncActionTest extends TestCase
{
    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new ReflectionClass(RecurringPaymentDetailsSyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncAndArrayAsModelWhichHasBillingPeriodSet()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $paymentDetails = [
            'BILLINGPERIOD' => 12,
        ];

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotSyncRequest()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new RecurringPaymentDetailsSyncAction();

        $action->execute(new stdClass());
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

        $request = new Sync([
            'BILLINGPERIOD' => 12,
        ]);

        $action->execute($request);
    }

    public function testShouldRequestGetRecurringPaymentsProfileDetailsActionIfProfileIdSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetRecurringPaymentsProfileDetails::class))
        ;

        $action = new RecurringPaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync([
            'BILLINGPERIOD' => 'aBillingPeriod',
            'PROFILEID' => 'anId',
        ]));
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
