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

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(array('BILLINGPERIOD' => 'foo'))),
            array(new $this->requestClass(new \ArrayObject(array('BILLINGPERIOD' => 'foo'))))
        );
    }
    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new $this->requestClass('foo')),
            array(new $this->requestClass(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CancelRecurringPaymentsProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CancelRecurringPaymentsProfileAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CancelRecurringPaymentsProfileAction();
    }

    /**
     * @test
     */
    public function shouldSupportManageRecurringPaymentsProfileStatusRequestAndArrayAccessAsModel()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $this->assertTrue(
            $action->supports(new Cancel(array('BILLINGPERIOD' => 'foo')))
        );

        $this->assertTrue(
            $action->supports(new Cancel(new \ArrayObject(array('BILLINGPERIOD' => 'foo'))))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotManageRecurringPaymentsProfileStatusRequest()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute($request = null)
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID fields are required.
     */
    public function throwIfProfileIdNotSetInModel()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $request = new Cancel(array('BILLINGPERIOD' => 'foo'));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldExecuteManageAndSyncActions()
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
