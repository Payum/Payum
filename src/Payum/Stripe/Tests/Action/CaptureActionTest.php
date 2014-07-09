<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\Http\GetRequestRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Core\Request\RenderTemplateRequest;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Keys;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction;
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAccessModel()
    {
        $action = new CaptureAction;

        $this->assertTrue($action->supports(new CaptureRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotArrayAccessModel()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new CaptureRequest(new \stdClass)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureRequest()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CaptureAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CaptureAction;

        $action->execute(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfModelHasAlreadyUsedToken()
    {
        $model = array(
            'card' => array('foo', 'bar')
        );

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest($model));
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
            ->with($this->isInstanceOf('Payum\Stripe\Request\Api\ObtainTokenRequest'))
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest($model));
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
            ->with($this->isInstanceOf('Payum\Stripe\Request\Api\CreateChargeRequest'))
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest($model));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }

}