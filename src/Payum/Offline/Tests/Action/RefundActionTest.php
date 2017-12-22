<?php
namespace Payum\Offline\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Refund;
use Payum\Offline\Action\RefundAction;
use Payum\Offline\Constants;

class RefundActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Offline\Action\RefundAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RefundAction();
    }

    /**
     * @test
     */
    public function shouldSupportRefundWithArrayAccessAsModel()
    {
        $action = new RefundAction();

        $request = new Refund($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotRefund()
    {
        $action = new RefundAction();

        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportRefundAndNotArrayAccessAsModel()
    {
        $action = new RefundAction();

        $request = new Refund(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new RefundAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSetStatusRefundedIfStatusSetToCaptured()
    {
        $action = new RefundAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_STATUS] = Constants::STATUS_CAPTURED;

        $request = new Refund($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_REFUNDED, $details[Constants::FIELD_STATUS]);
    }

    /**
     * @test
     */
    public function shouldNotSetStatusRefundedIfStatusNotSetToCaptured()
    {
        $action = new RefundAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;

        $request = new Refund($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertTrue(isset($details[Constants::FIELD_STATUS]));
        $this->assertEquals(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }
}
