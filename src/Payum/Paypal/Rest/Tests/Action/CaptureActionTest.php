<?php

namespace Payum\Paypal\Rest\Tests\Action;

use PayPal\Api\Payment as PaypalPayment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Core\Request\Capture;

class CaptureActionTest extends \PHPUnit\Framework\TestCase
{
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
    public function shouldSupportCaptureWithArrayObjectModel()
    {
        $action = new CaptureAction();

        $model = new \ArrayObject();

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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
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

        $request = new Capture($this->createMock(PaypalPayment::class));

        $this->assertTrue($action->supports($request));
        $this->assertTrue($action->supports(new Capture(new \ArrayObject)));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedApiContext()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }
}
