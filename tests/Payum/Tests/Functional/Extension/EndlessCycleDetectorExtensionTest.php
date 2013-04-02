<?php
namespace Payum\Tests\Functional\Extension;

use Payum\Action\PaymentAwareAction;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Payment;

class EndlessCycleDetectorExtensionTest extends \PHPUnit_Framework_TestCase 
{
    /**
     * @test
     * 
     * @expectedException \Payum\Exception\LogicException 
     * @expectedExceptionMessage Possible endless cycle detected. ::onPreExecute was called 10 times before reach the limit.
     */
    public function throwCycleRequestIfActionCallsMoreThenLimitAllows()
    {
        $cycledRequest = new \stdClass();

        $action = new RequireOtherRequestAction;
        $action->setSupportedRequest($cycledRequest);
        $action->setRequiredRequest($cycledRequest);

        $payment = new Payment();
        $payment->addExtension(new EndlessCycleDetectorExtension($limit = 10));
        $payment->addAction($action);

        $payment->execute($cycledRequest);
    }
}

class RequireOtherRequestAction extends PaymentAwareAction
{
    protected $supportedRequest;

    protected $requiredRequest;

    /**
     * @param $request
     */
    public function setSupportedRequest($request)
    {
        $this->supportedRequest = $request;
    }

    /**
     * @param $request
     */
    public function setRequiredRequest($request)
    {
        $this->requiredRequest = $request;
    }

    public function execute($request)
    {
        $this->payment->execute($this->requiredRequest);
    }

    public function supports($request)
    {
        return $this->supportedRequest === $request;
    }
}