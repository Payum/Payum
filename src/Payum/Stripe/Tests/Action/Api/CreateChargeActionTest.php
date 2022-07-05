<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCharge;

class CreateChargeActionTest extends \PHPUnit\Framework\TestCase
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
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
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
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreateChargeAction is not supported the request stdClass.');
        $action = new CreateChargeAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfNotCardNorCustomerSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The either card token or customer id has to be set.');
        $action = new CreateChargeAction();

        $action->execute(new CreateCharge([]));
    }

    /**
     * @test
     */
    public function throwIfCardAlreadyUsed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The token has already been used.');
        $action = new CreateChargeAction();

        $action->execute(new CreateCharge([
            'card' => ['foo'],
        ]));
    }
}
