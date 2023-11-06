<?php

namespace Payum\Paypal\Rest\Tests\Action;

use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Model\PaymentDetails;
use Payum\Core\Request\GetBinaryStatus;

class StatusActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\Rest\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldNotSupportStatusRequestWithNoPaymentAsModel()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testShouldSupportStatusRequestWithArrayObjectAsModel()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new \ArrayObject());

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotStatusRequest()
    {
        $action = new StatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new \stdClass());
    }

    public function testShouldMarkPendingIfStateCreated()
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

    public function testShouldMarkNewIfStateNotSet()
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

    public function testShouldMarkCapturedIfStateApproved()
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

    public function testShouldMarkCanceledIfStateCanceled()
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

    public function testShouldMarkUnknownIfStateIsSetAndSetUnknown()
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
