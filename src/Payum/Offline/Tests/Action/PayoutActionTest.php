<?php

namespace Payum\Offline\Tests\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Payout;
use Payum\Offline\Action\PayoutAction;
use Payum\Offline\Constants;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class PayoutActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportPayoutWithArrayAccessAsModel()
    {
        $action = new PayoutAction();

        $request = new Payout($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportNotPayout()
    {
        $action = new PayoutAction();

        $request = new stdClass();

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportPayoutAndNotArrayAccessAsModel()
    {
        $action = new PayoutAction();

        $request = new Payout(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new PayoutAction();

        $action->execute(new stdClass());
    }

    public function testShouldSetStatusPendingIfPayoutNotSet()
    {
        $action = new PayoutAction();

        $details = new ArrayObject();

        $request = new Payout($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    public function testShouldSetStatusPendingIfPayoutSetToFalse()
    {
        $action = new PayoutAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAYOUT] = false;

        $request = new Payout($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    public function testShouldSetStatusPayedoutIfPayoutSetToTrue()
    {
        $action = new PayoutAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAYOUT] = true;

        $request = new Payout($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_PAYEDOUT, $details[Constants::FIELD_STATUS]);
    }
}
