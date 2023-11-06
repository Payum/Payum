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

    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(GetPayoutStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldMarkNewIfAckNotSet()
    {
        $action = new GetPayoutStatusAction();

        $payout = [];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkPayedoutIfAckSuccess()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_SUCCESS
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    public function testShouldMarkPayedoutIfAckSuccessWithWarning()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_SUCCESS_WITH_WARNING
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    public function testShouldMarkFailedIfAckFailure()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_FAILURE
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkFailedIfAckFailureWithWarning()
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_FAILURE_WITH_WARNING
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkUnknownIfAckNotRecognized()
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
