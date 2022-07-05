<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Action\Api\CreatePlanAction;
use Payum\Stripe\Request\Api\CreatePlan;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreatePlanActionTest extends TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new ReflectionClass(CreatePlanAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new ReflectionClass(CreatePlanAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowNotSupportedApiIfNotKeysGivenAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new CreatePlanAction();

        $action->setApi('not keys instance');
    }

    public function testShouldSupportCreatePlanRequestWithArrayAccessModel()
    {
        $action = new CreatePlanAction();

        $this->assertTrue($action->supports(new CreatePlan([])));
    }

    public function testShouldNotSupportCreatePlanRequestWithNotArrayAccessModel()
    {
        $action = new CreatePlanAction();

        $this->assertFalse($action->supports(new CreatePlan(new stdClass())));
    }

    public function testShouldNotSupportNotCreatePlanRequest()
    {
        $action = new CreatePlanAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowRequestNotSupportedIfNotSupportedGiven()
    {
        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreatePlanAction is not supported the request stdClass.');
        $action = new CreatePlanAction();

        $action->execute(new stdClass());
    }
}
