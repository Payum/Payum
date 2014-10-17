<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\NotifyAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class NotifyActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Klarna\Checkout\Action\NotifyAction';

    protected $requestClass = 'Payum\Core\Request\Notify';

    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\NotifyAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutCompleteOnExecute()
    {
        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\UpdateOrder'))
            ->will($this->returnCallback(function(UpdateOrder $request) use ($testCase) {
                $model = $request->getModel();

                $testCase->assertEquals(Constants::STATUS_CREATED, $model['status']);
                $testCase->assertEquals('theLocation', $model['location']);
                $testCase->assertEquals('theOrderId', $model['merchant_reference']['orderid1']);
            }))
        ;
        $paymentMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction;
        $action->setPayment($paymentMock);

        $action->execute(new Notify(array(
            'status' => Constants::STATUS_CHECKOUT_COMPLETE,
            'location' => 'theLocation',
            'merchant_reference' => array(
                'orderid1' => 'theOrderId',
            )
        )));
    }

    /**
     * @test
     */
    public function shouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCheckoutInCompleteOnExecute()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction;
        $action->setPayment($paymentMock);

        $action->execute(new Notify(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'aLocation',
        )));
    }

    /**
     * @test
     */
    public function shouldNotUpdateOrderWithStatusCreatedIfCurrentStatusCreatedOnExecute()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction;
        $action->setPayment($paymentMock);

        $action->execute(new Notify(array(
            'status' => Constants::STATUS_CREATED,
            'location' => 'aLocation',
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}
