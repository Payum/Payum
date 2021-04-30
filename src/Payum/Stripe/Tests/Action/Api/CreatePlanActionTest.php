<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreatePlanAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreatePlan;

class CreatePlanActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreatePlanAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreatePlanAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CreatePlanAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCreatePlanRequestWithArrayAccessModel()
    {
        $action = new CreatePlanAction();

        $this->assertTrue($action->supports(new CreatePlan([])));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreatePlanRequestWithNotArrayAccessModel()
    {
        $action = new CreatePlanAction();

        $this->assertFalse($action->supports(new CreatePlan(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCreatePlanRequest()
    {
        $action = new CreatePlanAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreatePlanAction is not supported the request stdClass.');
        $action = new CreatePlanAction();

        $action->execute(new \stdClass());
    }
}
