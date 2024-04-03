<?php

namespace Payum\Offline\Tests\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Constants;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CaptureActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportCaptureWithArrayAccessAsModel(): void
    {
        $action = new CaptureAction();

        $request = new Capture($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportNotCapture(): void
    {
        $action = new CaptureAction();

        $request = new stdClass();

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportCaptureAndNotArrayAccessAsModel(): void
    {
        $action = new CaptureAction();

        $request = new Capture(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CaptureAction();

        $action->execute(new stdClass());
    }

    public function testShouldSetStatusPendingIfPaidNotSet(): void
    {
        $action = new CaptureAction();

        $details = new ArrayObject();

        $request = new Capture($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    public function testShouldSetStatusPendingIfPaidSetToFalse(): void
    {
        $action = new CaptureAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAID] = false;

        $request = new Capture($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    public function testShouldSetStatusCapturedIfPaidSetToTrue(): void
    {
        $action = new CaptureAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAID] = true;

        $request = new Capture($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_CAPTURED, $details[Constants::FIELD_STATUS]);
    }
}
