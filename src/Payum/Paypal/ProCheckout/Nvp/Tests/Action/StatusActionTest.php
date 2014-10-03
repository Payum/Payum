<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ProCheckout\Nvp\Api;

class StatusActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProCheckout\Nvp\Action\StatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
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
    public function shouldSupportGetStatusRequestWithArrayAsModel()
    {
        $action = new StatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus(array())));
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

        $this->assertFalse($action->supports(new GetHumanStatus(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StatusAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $status = new GetHumanStatus(array());

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfResultNotSupported()
    {
        $action = new StatusAction();

        $status = new GetHumanStatus(array(
            'RESULT' => 123,
        ));
        
        //guard
        $status->markNew();
        
        $action->execute($status);
        
        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfResultSuccess()
    {
        $action = new StatusAction();

        $status = new GetHumanStatus(array(
            'RESULT' => Api::RESULT_SUCCESS,
        ));

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }
}