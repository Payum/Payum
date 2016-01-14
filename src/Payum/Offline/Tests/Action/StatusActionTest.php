<?php
namespace Payum\Offline\Tests\Action;

use Payum\Offline\Constants;
use Payum\Core\Request\GetBinaryStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Offline\Action\StatusAction;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Offline\Action\StatusAction');

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
    public function shouldSupportStatusRequestWithArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = $this->createGetStatusStub($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotStatusRequest()
    {
        $action = new StatusAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessAsModel()
    {
        $action = new StatusAction();

        $request = $this->createGetStatusStub(new \stdClass());

        $this->assertFalse($action->supports($request));
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
    public function shouldMarkNewIfDetailsEmpty()
    {
        $request = new GetBinaryStatus(array());
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfStatusNotSet()
    {
        $request = new GetBinaryStatus(array());
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfStatusSetToPending()
    {
        $request = new GetBinaryStatus(array(
            Constants::FIELD_STATUS => Constants::STATUS_PENDING,
        ));
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfStatusSetToCaptured()
    {
        $request = new GetBinaryStatus(array(
            Constants::FIELD_STATUS => Constants::STATUS_CAPTURED,
        ));
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }
    
    /**
     * @test
     */
    public function shouldMarkCanceledIfStatusSetToCanceled()
    {
        $request = new GetBinaryStatus(array(
            Constants::FIELD_STATUS => Constants::STATUS_CANCELED,
        ));
        $request->markUnknown();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isCanceled());
    }
    
    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusNotRecognized()
    {
        $request = new GetBinaryStatus(array(
            Constants::FIELD_STATUS => 'some-foo-bar-status',
        ));
        $request->markCaptured();

        $action = new StatusAction();

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GetStatusInterface
     */
    protected function createGetStatusStub($model)
    {
        $status = $this->getMock('Payum\Core\Request\GetStatusInterface');

        $status
            ->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue($model))
        ;

        return $status;
    }
}
