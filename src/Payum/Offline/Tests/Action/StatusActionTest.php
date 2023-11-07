<?php

namespace Payum\Offline\Tests\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Offline\Action\StatusAction;
use Payum\Offline\Constants;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class StatusActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(StatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportStatusRequestWithArrayAccessAsModel(): void
    {
        $action = new StatusAction();

        $request = $this->createGetStatusStub($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportNotStatusRequest(): void
    {
        $action = new StatusAction();

        $request = new stdClass();

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportStatusRequestWithNotArrayAccessAsModel(): void
    {
        $action = new StatusAction();

        $request = $this->createGetStatusStub(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new StatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkNewIfDetailsEmpty(): void
    {
        $request = new GetBinaryStatus([]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkNewIfStatusNotSet(): void
    {
        $request = new GetBinaryStatus([]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkPendingIfStatusSetToPending(): void
    {
        $request = new GetBinaryStatus([
            Constants::FIELD_STATUS => Constants::STATUS_PENDING,
        ]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    public function testShouldMarkCapturedIfStatusSetToCaptured(): void
    {
        $request = new GetBinaryStatus([
            Constants::FIELD_STATUS => Constants::STATUS_CAPTURED,
        ]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    public function testShouldMarkPayedoutIfStatusSetToPayedout(): void
    {
        $request = new GetBinaryStatus([
            Constants::FIELD_STATUS => Constants::STATUS_PAYEDOUT,
        ]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    public function testShouldMarkRefundedIfStatusSetToRefunded(): void
    {
        $request = new GetBinaryStatus([
            Constants::FIELD_STATUS => Constants::STATUS_REFUNDED,
        ]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isRefunded());
    }

    public function testShouldMarkCanceledIfStatusSetToCanceled(): void
    {
        $request = new GetBinaryStatus([
            Constants::FIELD_STATUS => Constants::STATUS_CANCELED,
        ]);
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }

    public function testShouldMarkUnknownIfStatusNotRecognized(): void
    {
        $request = new GetBinaryStatus([
            Constants::FIELD_STATUS => 'some-foo-bar-status',
        ]);
        $request->markCaptured();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
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
