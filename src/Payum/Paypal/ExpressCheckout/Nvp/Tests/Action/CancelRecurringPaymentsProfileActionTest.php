<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelRecurringPaymentsProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;

class CancelRecurringPaymentsProfileActionTest extends GenericActionTest
{
    /**
     * @var Generic
     */
    protected $requestClass = \Payum\Core\Request\Cancel::class;

    /**
     * @var ActionInterface
     */
    protected $actionClass = \Payum\Paypal\ExpressCheckout\Nvp\Action\CancelRecurringPaymentsProfileAction::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield [
            new $this->requestClass([
                'BILLINGPERIOD' => 'foo',
            ]), ];
        yield [new $this->requestClass(new \ArrayObject([
            'BILLINGPERIOD' => 'foo',
        ]))];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [new $this->requestClass('foo')];
        yield [new $this->requestClass(new \stdClass())];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CancelRecurringPaymentsProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CancelRecurringPaymentsProfileAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportManageRecurringPaymentsProfileStatusRequestAndArrayAccessAsModel()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $this->assertTrue(
            $action->supports(new Cancel([
                'BILLINGPERIOD' => 'foo',
            ]))
        );

        $this->assertTrue(
            $action->supports(new Cancel(new \ArrayObject([
                'BILLINGPERIOD' => 'foo',
            ])))
        );
    }

    public function testShouldNotSupportAnythingNotManageRecurringPaymentsProfileStatusRequest()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute($request = null)
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CancelRecurringPaymentsProfileAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfProfileIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILEID fields are required.');
        $action = new CancelRecurringPaymentsProfileAction();

        $request = new Cancel([
            'BILLINGPERIOD' => 'foo',
        ]);

        $action->execute($request);
    }

    public function testShouldExecuteManageAndSyncActions()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(ManageRecurringPaymentsProfileStatus::class)],
                [$this->isInstanceOf(Sync::class)]
            )
        ;

        $action = new CancelRecurringPaymentsProfileAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel([
            'BILLINGPERIOD' => 'Month',
            'PROFILEID' => 'profile-ID',
        ]);

        $action->execute($request);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
