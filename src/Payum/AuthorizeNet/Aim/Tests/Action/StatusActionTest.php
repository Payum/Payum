<?php

namespace Payum\AuthorizeNet\Aim\Tests\Action;

use AuthorizeNetAIM_Response;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = StatusAction::class;

    protected $requestClass = GetHumanStatus::class;

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new ArrayObject());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfResponseCodeNotSetInModel(): void
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new ArrayObject());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkUnknownIfResponseCodeUnknown(): void
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = 'foobarbaz';

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCapturedStatusIfArrayObjectHasResponseCodeApproved(): void
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = AuthorizeNetAIM_Response::APPROVED;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkFailedStatusIfArrayObjectHasResponseCodeError(): void
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = AuthorizeNetAIM_Response::ERROR;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkPendingStatusIfArrayObjectHasResponseCodeHeld(): void
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = AuthorizeNetAIM_Response::HELD;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkCanceledStatusIfArrayObjectHasResponseCodeDeclined(): void
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = AuthorizeNetAIM_Response::DECLINED;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @return MockObject|GetStatusInterface
     */
    protected function createGetStatusStub($model)
    {
        $status = $this->createMock(GetStatusInterface::class);

        $status
            ->method('getModel')
            ->willReturn($model)
        ;

        return $status;
    }
}
