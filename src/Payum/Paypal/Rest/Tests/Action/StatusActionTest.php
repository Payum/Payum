<?php

namespace Payum\Paypal\Rest\Tests\Action;

use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Core\Request\GetBinaryStatus;

class StatusActionTest extends \PHPUnit\Framework\TestCase
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
    public function shouldNotSupportStatusRequestWithNoPaymentAsModel()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportStatusRequestWithArrayObjectAsModel()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new \ArrayObject());

        $this->assertTrue($action->supports($request));
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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfStateCreated()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('created');

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isPending());

        $model = new \ArrayObject(['state' => 'created']);
        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isPending());
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

        $model = new \ArrayObject();
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

        $model = new \ArrayObject(['state' => 'approved']);
        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledIfStateCanceled()
    {
        $action = new StatusAction();

        $model = new PaymentDetails();
        $model->setState('cancelled');

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());

        $model = new \ArrayObject(['state' => 'cancelled']);
        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
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

        $model = new \ArrayObject(['state' => 'random']);
        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}
