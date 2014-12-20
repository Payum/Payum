<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\StatusAction;

class StatusActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\GetHumanStatus';

    protected $actionClass = 'Payum\Stripe\Action\StatusAction';

    /**
     * @test
     */
    public function shouldMarkFailedIfModelHasErrorSet()
    {
        $action = new StatusAction();

        $model = array(
            'error' => array('code' => 'foo'),
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();

        $model = array();

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkNewIfModelHasNotCardSet()
    {
        $action = new StatusAction();

        $model = array();

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPendingIfModelHasNotUsedTokenSet()
    {
        $action = new StatusAction();

        $model = array(
            'card' => 'not-used-token',
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isPending());
    }

    /**
     * @test
     */
    public function shouldMarkCapturedIfModelHasSuccefullyUsedTokenSet()
    {
        $action = new StatusAction();

        $model = array(
            'card' => array('foo'),
            'captured' => true,
            'paid' => true,
        );

        $action->execute($status = new GetHumanStatus($model));

        $this->assertTrue($status->isCaptured());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfStatusCouldBeGuessed()
    {
        $action = new StatusAction();

        $model = array(
            'card' => array('foo'),
            'captured' => false,
            'paid' => true,
        );

        $status = new GetHumanStatus($model);
        $status->markPending();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }
}
