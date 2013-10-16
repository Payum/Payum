<?php

namespace Payum\Paypal\Rest\Tests\Action;

use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Request\BinaryMaskStatusRequest;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\Rest\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StatusAction();
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNoPaymentAsModel()
    {
        $action = new StatusAction();

        $request = new BinaryMaskStatusRequest(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfStateCreated()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('created');

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfStateNotSet()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkSuccessIfStateApproved()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('approved');

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isSuccess());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStateIsSetAndSetUnknown()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('random');

        $request = new BinaryMaskStatusRequest($model);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}