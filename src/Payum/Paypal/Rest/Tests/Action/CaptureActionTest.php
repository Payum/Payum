<?php

namespace Payum\Paypal\Rest\Tests\Action;

use PayPal\Api\Payment as PaypalPayment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Core\Request\Capture;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureWithPaymentSdkModel()
    {
        $action = new CaptureAction();

        $model = new PaymentDetails();

        $request = new Capture($model);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCapturePaymentSdkModel()
    {
        $action = new CaptureAction();

        $request = new Capture(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCapture()
    {
        $action = new CaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldSupportCapture()
    {
        $action = new CaptureAction();

        $request = new Capture($this->getMock(PaypalPayment::class));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportApiContext()
    {
        $action = new CaptureAction();

        /** @var OAuthTokenCredential $tokenMock */
        $tokenMock = $this->getMock(OAuthTokenCredential::class, [], [], '', false);

        $apiContext = new ApiContext($tokenMock);

        $action->setApi($apiContext);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfNotSupportedApiContext()
    {
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }
}
