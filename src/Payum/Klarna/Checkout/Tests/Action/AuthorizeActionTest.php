<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\RenderTemplate;
use Payum\Klarna\Checkout\Action\AuthorizeAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class AuthorizeActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\AuthorizeAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AuthorizeAction('aTemplate');
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeWithArrayAsModel()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertTrue($action->supports(new Authorize(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorize()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAuthorizeWithNotArrayAccessModel()
    {
        $action = new AuthorizeAction('aTemplate');

        $this->assertFalse($action->supports(new Authorize(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new AuthorizeAction('aTemplate');

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Reply\HttpResponse
     */
    public function shouldSubExecuteSyncIfModelHasLocationSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
        ;

        $action = new AuthorizeAction('aTemplate');
        $action->setPayment($paymentMock);

        $action->execute(new Authorize(array(
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
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\CreateOrder'))
            ->will($this->returnCallback(function (CreateOrder $request) use ($orderMock) {
                $request->setOrder($orderMock);
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new AuthorizeAction('aTemplate');
        $action->setPayment($paymentMock);

        $model = new \ArrayObject();

        $action->execute(new Authorize($model));

        $this->assertEquals('fooVal', $model['foo']);
        $this->assertEquals('barVal', $model['bar']);
        $this->assertEquals('theLocation', $model['location']);
    }

    /**
     * @test
     */
    public function shouldThrowReplyWhenStatusCheckoutIncomplete()
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
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($testCase, $expectedTemplateName, $expectedContext, $expectedContent) {
                $testCase->assertEquals($expectedTemplateName, $request->getTemplateName());
                $testCase->assertEquals($expectedContext, $request->getContext());

                $request->setResult($expectedContent);
            }))
        ;

        $action = new AuthorizeAction($expectedTemplateName);
        $action->setPayment($paymentMock);

        try {
            $action->execute(new Authorize(array(
                'location' => 'aLocation',
                'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
                'gui' => array('snippet' => $snippet),
            )));
        } catch (HttpResponse $reply) {
            $this->assertEquals($expectedContent, $reply->getContent());

            return;
        }

        $this->fail('Exception expected to be throw');
    }

    /**
     * @test
     */
    public function shouldNotThrowReplyWhenStatusNotSet()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setPayment($this->createPaymentMock());

        $action->execute(new Authorize(array(
            'location' => 'aLocation',
            'gui' => array('snippet' => 'theSnippet'),
        )));
    }

    /**
     * @test
     */
    public function shouldNotThrowReplyWhenStatusCreated()
    {
        $action = new AuthorizeAction('aTemplate');
        $action->setPayment($this->createPaymentMock());

        $action->execute(new Authorize(array(
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
