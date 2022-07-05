<?php

namespace Payum\Offline\Tests\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Payum\Offline\Action\RefundAction;
use Payum\Offline\Constants;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class RefundActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(RefundAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldSupportRefundWithArrayAccessAsModel()
    {
        $action = new RefundAction();

        $request = new Refund($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportNotRefund()
    {
        $action = new RefundAction();

        $request = new stdClass();

        $this->assertFalse($action->supports($request));
    }

    public function testShouldNotSupportRefundAndNotArrayAccessAsModel()
    {
        $action = new RefundAction();

        $request = new Refund(new stdClass());

        $this->assertFalse($action->supports($request));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new RefundAction();

        $action->execute(new stdClass());
    }

    public function testShouldSetStatusRefundedIfStatusSetToCaptured()
    {
        $action = new RefundAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_STATUS] = Constants::STATUS_CAPTURED;

        $request = new Refund($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_REFUNDED, $details[Constants::FIELD_STATUS]);
    }

    public function testShouldNotSetStatusRefundedIfStatusNotSetToCaptured()
    {
        $action = new RefundAction();

        $details = new ArrayObject();
        $details[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;

        $request = new Refund($details);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $this->assertArrayHasKey(Constants::FIELD_STATUS, $details);
        $this->assertSame(Constants::STATUS_PENDING, $details[Constants::FIELD_STATUS]);
    }
}
