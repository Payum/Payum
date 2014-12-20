<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;

class NotifyActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Notify';

    protected $actionClass = 'Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction';

    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldSubExecuteSyncWithSameModel()
    {
        $expectedModel = array('foo' => 'fooVal');

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new NotifyAction();
        $action->setPayment($paymentMock);

        $action->execute(new Notify($expectedModel));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
