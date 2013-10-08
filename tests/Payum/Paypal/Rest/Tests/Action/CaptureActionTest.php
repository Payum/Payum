<?php

namespace Payum\Paypal\Rest\Tests\Action;

use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Request\CaptureRequest;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\Rest\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

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
    public function shouldNotSupportCaptureRequestAndNotArrayAccessAsModel()
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
     *
     * @expectedException \Payum\Exception\UnsupportedApiException
     */
    public function throwIfNotSupportedApiContext()
    {
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }
}
