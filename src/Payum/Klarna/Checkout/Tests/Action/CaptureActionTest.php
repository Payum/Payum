<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\RenderTemplateRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Klarna\Checkout\Action\CaptureAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrderRequest;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction('aTemplate');
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequestWithArrayAsModel()
    {
        $action = new CaptureAction('aTemplate');

        $this->assertTrue($action->supports(new CaptureRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCaptureRequest()
    {
        $action = new CaptureAction('aTemplate');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotArrayAccessModel()
    {
        $action = new CaptureAction('aTemplate');

        $this->assertFalse($action->supports(new CaptureRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CaptureAction('aTemplate');

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Request\Http\ResponseInteractiveRequest
     */
    public function shouldSubExecuteSyncRequestIfModelHasLocationSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplateRequest'))
        ;

        $action = new CaptureAction('aTemplate');
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
        $orderMock = $this->createOrderMock();
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array(
                'foo' => 'fooVal',
                'bar' => 'barVal',
            )))
        ;
        $orderMock
            ->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue('theLocation'))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\CreateOrderRequest'))
            ->will($this->returnCallback(function(CreateOrderRequest $request) use ($orderMock) {
                $request->setOrder($orderMock);
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;

        $action = new CaptureAction('aTemplate');
        $action->setPayment($paymentMock);

        $model = new \ArrayObject();

        $action->execute(new CaptureRequest($model));

        $this->assertEquals('fooVal', $model['foo']);
        $this->assertEquals('barVal', $model['bar']);
        $this->assertEquals('theLocation', $model['location']);
    }

    /**
     * @test
     */
    public function shouldThrowInteractiveRequestWhenStatusCheckoutIncomplete()
    {
        $snippet = 'theSnippet';
        $expectedContent = 'theTemplateContent';
        $expectedTemplateName = 'theTemplateName';
        $expectedContext = array('snippet' => $snippet);

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplateRequest'))
            ->will($this->returnCallback(function(RenderTemplateRequest $request) use($testCase, $expectedTemplateName, $expectedContext, $expectedContent) {
                $testCase->assertEquals($expectedTemplateName, $request->getTemplateName());
                $testCase->assertEquals($expectedContext, $request->getContext());

                $request->setResult($expectedContent);
            }))
        ;

        $action = new CaptureAction($expectedTemplateName);
        $action->setPayment($paymentMock);

        try {
            $action->execute(new CaptureRequest(array(
                'location' => 'aLocation',
                'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
                'gui' => array('snippet' => $snippet),
            )));
        } catch (ResponseInteractiveRequest $interactiveRequest) {
            $this->assertEquals($expectedContent, $interactiveRequest->getContent());

            return;
        }

        $this->fail('Exception expected to be throw');
    }

    /**
     * @test
     */
    public function shouldNotThrowInteractiveRequestWhenStatusNotSet()
    {
        $action = new CaptureAction('aTemplate');
        $action->setPayment($this->createPaymentMock());

        $action->execute(new CaptureRequest(array(
            'location' => 'aLocation',
            'gui' => array('snippet' => 'theSnippet'),
        )));
    }

    /**
     * @test
     */
    public function shouldNotThrowInteractiveRequestWhenStatusCreated()
    {
        $action = new CaptureAction('aTemplate');
        $action->setPayment($this->createPaymentMock());

        $action->execute(new CaptureRequest(array(
            'location' => 'aLocation',
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}