<?php

namespace Payum\Paypal\Rest\Tests\Action;

use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Core\Request\GetBinaryStatus;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\Rest\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
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

        $request = new GetBinaryStatus(new \stdClass());

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
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
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

        $request = new GetBinaryStatus($model);

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

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfStateApproved()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('approved');

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStateIsSetAndSetUnknown()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('random');

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}
