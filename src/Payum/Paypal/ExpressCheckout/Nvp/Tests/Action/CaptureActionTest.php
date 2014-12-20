<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Capture';

    protected $actionClass = 'Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction';

    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldSetZeroPaymentActionAsSell()
    {
        $action = new CaptureAction();
        $action->setPayment($this->createPaymentMock());

        $action->execute($request = new Capture(array()));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertEquals(Api::PAYMENTACTION_SALE, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenNotSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout'))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array()));
    }

    /**
     * @test
     */
    public function shouldNotExecuteAnythingIfSetExpressCheckoutActionFails()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout'))
            ->will($this->returnCallback(function (SetExpressCheckout $request) {
                $model = $request->getModel();

                $model['L_ERRORCODE0'] = 'aCode';
            }))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array()));
    }

    /**
     * @test
     */
    public function shouldSetTokenTargetUrlAsReturnUrlIfCapturePassedWithToken()
    {
        $testCase = $this;

        $expectedTargetUrl = 'theTargetUrl';

        $token = new Token();
        $token->setTargetUrl($expectedTargetUrl);
        $token->setDetails(array());

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout'))
            ->will($this->returnCallback(function ($request) use ($testCase, $expectedTargetUrl) {
                $model = $request->getModel();

                $testCase->assertEquals($expectedTargetUrl, $model['RETURNURL']);
            }))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $request = new Capture($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldSetTokenTargetUrlAsCancelUrlIfCapturePassedWithToken()
    {
        $testCase = $this;

        $expectedCancelUrl = 'theCancelUrl';

        $token = new Token();
        $token->setTargetUrl($expectedCancelUrl);
        $token->setDetails(array());

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout'))
            ->will($this->returnCallback(function ($request) use ($testCase, $expectedCancelUrl) {
                $model = $request->getModel();

                $testCase->assertEquals($expectedCancelUrl, $model['CANCELURL']);
            }))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $request = new Capture($token);
        $request->setModel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotRequestSetExpressCheckoutActionAndAuthorizeActionIfTokenSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
        )));
    }

    /**
     * @test
     */
    public function shouldRequestDoExpressCheckoutPaymentActionIfCheckoutStatusNotInitiatedAndPayerIdSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment'))
        ;
        $paymentMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 5,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfPayerIdNotSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'PAYERID' => null,
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfCheckoutStatusOtherThenNotInitiatedSetInModel()
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
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS,
        )));
    }

    /**
     * @test
     */
    public function shouldNotRequestDoExpressCheckoutPaymentActionIfAmountZero()
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
            ->with($this->isInstanceOf('Payum\Core\Request\Sync'))
        ;

        $action = new CaptureAction();
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'TOKEN' => 'aToken',
            'CHECKOUTSTATUS' => Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED,
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_AMT' => 0,
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
