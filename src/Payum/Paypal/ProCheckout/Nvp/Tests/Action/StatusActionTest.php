<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ProCheckout\Nvp\Api;

class StatusActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Paypal\ProCheckout\Nvp\Action\StatusAction';

    protected $requestClass = 'Payum\Core\Request\GetHumanStatus';

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