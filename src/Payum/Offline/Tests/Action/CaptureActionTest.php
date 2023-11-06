<?php
namespace Payum\Offline\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Constants;
use Payum\Core\Request\Capture;

class CaptureActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Offline\Action\CaptureAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportCaptureWithArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $request = new Capture($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportNotCapture()
    {
        $action = new CaptureAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportCaptureAndNotArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $request = new Capture(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }

    public function testShouldSetStatusPendingIfPaidNotSet()
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

    public function testShouldSetStatusPendingIfPaidSetToFalse()
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

    public function testShouldSetStatusCapturedIfPaidSetToTrue()
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
