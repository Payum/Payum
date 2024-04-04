<?php
namespace Payum\Offline\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Payout;
use Payum\Offline\Action\PayoutAction;
use Payum\Offline\Constants;

class PayoutActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Offline\Action\PayoutAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportPayoutWithArrayAccessAsModel()
    {
        $action = new PayoutAction();

        $request = new Payout($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportNotPayout()
    {
        $action = new PayoutAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportPayoutAndNotArrayAccessAsModel()
    {
        $action = new PayoutAction();

        $request = new Payout(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new PayoutAction();

        $action->execute(new \stdClass());
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
