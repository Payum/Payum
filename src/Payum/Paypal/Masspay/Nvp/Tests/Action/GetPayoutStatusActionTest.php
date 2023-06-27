<?php

namespace Payum\Paypal\Masspay\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Masspay\Nvp\Action\GetPayoutStatusAction;
use Payum\Paypal\Masspay\Nvp\Api;
use ReflectionClass;

class GetPayoutStatusActionTest extends GenericActionTest
{
    /**
     * @var class-string<GetHumanStatus>
     */
    protected $requestClass = GetHumanStatus::class;

    /**
     * @var class-string<GetPayoutStatusAction>
     */
    protected $actionClass = GetPayoutStatusAction::class;

    public function testShouldImplementsActionInterface(): void
    {
        $rc = new ReflectionClass(GetPayoutStatusAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldMarkNewIfAckNotSet(): void
    {
        $action = new GetPayoutStatusAction();

        $payout = [];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    public function testShouldMarkPayedoutIfAckSuccess(): void
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_SUCCESS,
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    public function testShouldMarkPayedoutIfAckSuccessWithWarning(): void
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_SUCCESS_WITH_WARNING,
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isPayedout());
    }

    public function testShouldMarkFailedIfAckFailure(): void
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_FAILURE,
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkFailedIfAckFailureWithWarning(): void
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => Api::ACK_FAILURE_WITH_WARNING,
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    public function testShouldMarkUnknownIfAckNotRecognized(): void
    {
        $action = new GetPayoutStatusAction();

        $payout = [
            'ACK' => 'foo',
        ];

        $request = new GetHumanStatus($payout);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}
