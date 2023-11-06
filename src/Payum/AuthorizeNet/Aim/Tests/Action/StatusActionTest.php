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

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new ArrayObject());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfResponseCodeNotSetInModel()
    {
        $action = new StatusAction();

        $request = new GetBinaryStatus(new ArrayObject());

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkUnknownIfResponseCodeUnknown()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = 'foobarbaz';

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    public function testShouldMarkCapturedStatusIfArrayObjectHasResponseCodeApproved()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::APPROVED;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkFailedStatusIfArrayObjectHasResponseCodeError()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::ERROR;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkPendingStatusIfArrayObjectHasResponseCodeHeld()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::HELD;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkCanceledStatusIfArrayObjectHasResponseCodeDeclined()
    {
        $action = new StatusAction();

        $model = new ArrayObject();
        $model['response_code'] = \AuthorizeNetAIM_Response::DECLINED;

        $request = new GetBinaryStatus($model);

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GetStatusInterface
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
