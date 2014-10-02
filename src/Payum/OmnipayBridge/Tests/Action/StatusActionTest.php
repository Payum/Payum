<?php
namespace Payum\OmnipayBridge\Tests\Action;

use Payum\Core\Request\GetBinaryStatus;
use Payum\OmnipayBridge\Action\StatusAction;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\Action\StatusAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StatusAction;
    }

    /**
     * @test
     */
    public function shouldSupportBinaryMaskStatusRequestWithArrayAsModel()
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(
            new GetBinaryStatus(array())
        ));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStatusRequest()
    {
        $action = new StatusAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new StatusAction;

        $this->assertFalse($action->supports(new GetBinaryStatus(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new StatusAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusNotSupported()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus(array(
            '_status' => 'not-supported-status',
        ));

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus(array());

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfOrderStatusNotSet()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus(array());

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfStatusCaptured()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus(array(
            '_status' => 'captured',
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfStatusFailed()
    {
        $action = new StatusAction();

        $status = new GetBinaryStatus(array(
            '_status' => 'failed',
        ));

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }
}