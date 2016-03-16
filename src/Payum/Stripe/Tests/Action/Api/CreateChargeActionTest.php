<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCharge;

class CreateChargeActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreateChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateChargeAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CreateChargeAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CreateChargeAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCreateChargeRequestWithArrayAccessModel()
    {
        $action = new CreateChargeAction();

        $this->assertTrue($action->supports(new CreateCharge(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreateChargeRequestWithNotArrayAccessModel()
    {
        $action = new CreateChargeAction();

        $this->assertFalse($action->supports(new CreateCharge(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCreateChargeRequest()
    {
        $action = new CreateChargeAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreateChargeAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreateChargeAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The either card token or customer id has to be set.
     */
    public function throwIfNotCardNorCustomerSet()
    {
        $action = new CreateChargeAction();

        $action->execute(new CreateCharge([]));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The token has already been used.
     */
    public function throwIfCardAlreadyUsed()
    {
        $action = new CreateChargeAction();

        $action->execute(new CreateCharge([
            'card' => ['foo'],
        ]));
    }
}
