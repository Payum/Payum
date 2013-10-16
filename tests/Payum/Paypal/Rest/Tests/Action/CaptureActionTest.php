<?php

namespace Payum\Paypal\Rest\Tests\Action;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Request\CaptureRequest;

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
    public function shouldSupportCaptureRequestWithPaymentSdkModel()
    {
        $action = new CaptureAction();

        $model = new PaymentDetails();

        $request = new CaptureRequest($model);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureRequestWithNotPaymentSdkModel()
    {
        $action = new CaptureAction();

        $request = new CaptureRequest(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCaptureRequest()
    {
        $action = new CaptureAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportCaptureRequest()
    {
        $action = new CaptureAction();

        $request = new CaptureRequest($this->getMock('PayPal\Api\Payment'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportApiContext()
    {
        $action = new CaptureAction();

        $tokenMock = $this->getMockBuilder('PayPal\Auth\OAuthTokenCredential')
            ->disableOriginalConstructor()
            ->getMock();

        $apiContext = new ApiContext($tokenMock);

        $action->setApi($apiContext);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\UnsupportedApiException
     */
    public function throwIfNotSupportedApiContext()
    {
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }
}