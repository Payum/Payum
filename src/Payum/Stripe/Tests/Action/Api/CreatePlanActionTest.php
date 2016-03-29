<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreatePlanAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreatePlan;

class CreatePlanActionTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreatePlanAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CreatePlanAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreatePlanAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreatePlanAction();

        $action->execute(new \stdClass());
    }
}
