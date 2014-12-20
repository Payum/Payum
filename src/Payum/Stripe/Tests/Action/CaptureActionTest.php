<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\CaptureAction;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Capture';

    protected $actionClass = 'Payum\Stripe\Action\CaptureAction';

    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldDoNothingIfModelHasAlreadyUsedToken()
    {
        $model = array(
            'card' => array('foo', 'bar'),
        );

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture($model));
    }

    /**
     * @test
     */
    public function shouldSubExecuteObtainTokenReqeustIfTokenNotSet()
    {
        $model = array();

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Stripe\Request\Api\ObtainToken'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture($model));
    }

    /**
     * @test
     */
    public function shouldSubExecuteCreateChargeIfTokenSetButNotUsed()
    {
        $model = array(
            'card' => 'notUsedToken',
        );

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Stripe\Request\Api\CreateCharge'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture($model));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
