<?php
namespace Payum\Offline\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Offline\Action\CaptureAction;
use Payum\Offline\Constants;
use Payum\Core\Request\Capture;

class CaptureActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Offline\Action\CaptureAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CaptureAction();
    }

    /**
     * @test
     */
    public function shouldSupportCaptureWithArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $request = new Capture($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCapture()
    {
        $action = new CaptureAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCaptureAndNotArrayAccessAsModel()
    {
        $action = new CaptureAction();

        $request = new Capture(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CaptureAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetStatusPendingIfPaidNotSet()
    {
        $action = new CaptureAction();

        $details = new ArrayObject();

        $request = new Capture($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    /**
     * @test
     */
    public function shouldSetStatusPendingIfPaidSetToFalse()
    {
        $action = new CaptureAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAID] = false;

        $request = new Capture($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    /**
     * @test
     */
    public function shouldSetStatusCapturedIfPaidSetToTrue()
    {
        $action = new CaptureAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAID] = true;

        $request = new Capture($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_CAPTURED, $details[Constants::FIELD_STATUS]);
    }
}
