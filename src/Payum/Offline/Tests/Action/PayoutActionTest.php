<?php
namespace Payum\Offline\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Payout;
use Payum\Offline\Action\PayoutAction;
use Payum\Offline\Constants;

class PayoutActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Offline\Action\PayoutAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayoutAction();
    }

    /**
     * @test
     */
    public function shouldSupportPayoutWithArrayAccessAsModel()
    {
        $action = new PayoutAction();

        $request = new Payout($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotPayout()
    {
        $action = new PayoutAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportPayoutAndNotArrayAccessAsModel()
    {
        $action = new PayoutAction();

        $request = new Payout(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PayoutAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetStatusPendingIfPayoutNotSet()
    {
        $action = new PayoutAction();

        $details = new ArrayObject();

        $request = new Payout($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    /**
     * @test
     */
    public function shouldSetStatusPendingIfPayoutSetToFalse()
    {
        $action = new PayoutAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAYOUT] = false;

        $request = new Payout($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }

    /**
     * @test
     */
    public function shouldSetStatusPayedoutIfPayoutSetToTrue()
    {
        $action = new PayoutAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_PAYOUT] = true;

        $request = new Payout($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_PAYEDOUT, $details[Constants::FIELD_STATUS]);
    }
}
