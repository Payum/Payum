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
    protected $requestClass = 'Payum\Core\Request\Cancel';

    /**
     * @var ActionInterface
     */
    protected $actionClass = 'Payum\Paypal\ExpressCheckout\Nvp\Action\CancelRecurringPaymentsProfileAction';

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(array('BILLINGPERIOD' => 'foo')));
        yield array(new $this->requestClass(new \ArrayObject(array('BILLINGPERIOD' => 'foo'))));
    }
    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array(new $this->requestClass('foo'));
        yield array(new $this->requestClass(new \stdClass()));
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
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
            $action->supports(new Cancel(array('BILLINGPERIOD' => 'foo')))
        );

        $this->assertTrue(
            $action->supports(new Cancel(new \ArrayObject(array('BILLINGPERIOD' => 'foo'))))
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

        $request = new Cancel(array('BILLINGPERIOD' => 'foo'));

        $action->execute($request);
    }

    public function testShouldExecuteManageAndSyncActions()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                array($this->isInstanceOf(ManageRecurringPaymentsProfileStatus::class)),
                array($this->isInstanceOf(Sync::class))
            )
        ;

        $action = new CancelRecurringPaymentsProfileAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'BILLINGPERIOD' => 'Month',
            'PROFILEID' => 'profile-ID',
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
