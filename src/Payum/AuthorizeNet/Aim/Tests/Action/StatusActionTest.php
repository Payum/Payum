<?php

namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Tests\GenericActionTest;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\StatusAction';

    protected $requestClass = 'Payum\Core\Request\GetHumanStatus';

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new ArrayObject());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfResponseCodeNotSetInModel()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new ArrayObject());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfResponseCodeUnknown()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = 'foobarbaz';

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedStatusIfArrayObjectHasResponseCodeApproved()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::APPROVED;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkFailedStatusIfArrayObjectHasResponseCodeError()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::ERROR;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkPendingStatusIfArrayObjectHasResponseCodeHeld()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::HELD;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkCanceledStatusIfArrayObjectHasResponseCodeDeclined()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::DECLINED;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GetStatusInterface
     */
    protected function createGetStatusStub($model)
    {
        $status = $this->createMock('Payum\Core\Request\GetStatusInterface');

        $status
            ->method('getModel')
            ->willReturn($model)
        ;

        return $status;
    }
}
