<?php
namespace Payum\Paypal\Masspay\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Masspay\Nvp\Action\GetPayoutStatusAction;
use Payum\Paypal\Masspay\Nvp\Api;

class GetPayoutStatusActionTest extends GenericActionTest
{
    protected $requestClass = GetHumanStatus::class;

    protected $actionClass = GetPayoutStatusAction::class;

    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(GetPayoutStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldMarkNewIfAckNotSet()
    {
        $action = new GetPayoutStatusAction();

        $payout = [];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function shouldMarkPayedoutIfAckSuccess()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_SUCCESS
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    /**
     * @test
     */
    public function shouldMarkPayedoutIfAckSuccessWithWarning()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_SUCCESS_WITH_WARNING
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAckFailure()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_FAILURE
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkFailedIfAckFailureWithWarning()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_FAILURE_WITH_WARNING
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function shouldMarkUnknownIfAckNotRecognized()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => 'foo'
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}
