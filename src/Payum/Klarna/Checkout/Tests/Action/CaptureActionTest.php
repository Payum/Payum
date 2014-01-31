<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\CaptureRequest;
use Payum\Klarna\Checkout\Action\CaptureAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwreAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\CaptureAction');

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
    public function shouldSupportCaptureRequestWithArrayAsModel()
    {
        $action = new CaptureAction();

        $this->assertTrue($action->supports(new CaptureRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new CaptureAction;

        $this->assertFalse($action->supports(new \stdClass()));
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CaptureAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Klarna\Checkout\Request\Api\ShowSnippetInteractiveRequest
     */
    public function shouldSubExecuteUpdateOrderRequestIfStatusNotEqualsCreatedAndLocationSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\UpdateOrderRequest'))
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'aLocation',
        )));
    }

    /**
     * @test
     */
    public function shouldSubExecuteCreateOrderRequestIfStatusAndLocationNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\CreateOrderRequest'))
        ;

        $action = new CaptureAction;
        $action->setPayment($paymentMock);

        $action->execute(new CaptureRequest(array()));
    }

    /**
     * @test
     */
    public function shouldThrowInteractiveRequestWhenStatusCheckoutIncomplete()
    {
        $action = new CaptureAction;
        $action->setPayment($this->createPaymentMock());

        try {
            $action->execute(new CaptureRequest(array(
                'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
                'gui' => array('snippet' => 'theSnippet'),
            )));
        } catch (ShowSnippetInteractiveRequest $interactiveRequest) {
            $this->assertEquals('theSnippet', $interactiveRequest->getSnippet());

            return;
        }

        $this->fail('Exception expected to be throw');
    }

    /**
     * @test
     */
    public function shouldThrowInteractiveRequestWhenStatusCheckoutComplete()
    {
        $action = new CaptureAction;
        $action->setPayment($this->createPaymentMock());

        try {
            $action->execute(new CaptureRequest(array(
                'status' => Constants::STATUS_CHECKOUT_COMPLETE,
                'gui' => array('snippet' => 'theSnippet'),
            )));
        } catch (ShowSnippetInteractiveRequest $interactiveRequest) {
            $this->assertEquals('theSnippet', $interactiveRequest->getSnippet());

            return;
        }

        $this->fail('Exception expected to be throw');
    }

    /**
     * @test
     */
    public function shouldNotThrowInteractiveRequestWhenStatusNotSet()
    {
        $action = new CaptureAction;
        $action->setPayment($this->createPaymentMock());

        $action->execute(new CaptureRequest(array(
            'gui' => array('snippet' => 'theSnippet'),
        )));
    }

    /**
     * @test
     */
    public function shouldNotThrowInteractiveRequestWhenStatusCreated()
    {
        $action = new CaptureAction;
        $action->setPayment($this->createPaymentMock());

        $action->execute(new CaptureRequest(array(
            'status' => Constants::STATUS_CREATED,
            'gui' => array('snippet' => 'theSnippet'),
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}