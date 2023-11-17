<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Action\Api\CreateChargeAction;
use Payum\Stripe\Request\Api\CreateCharge;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateChargeActionTest extends TestCase
{
    public function testShouldImplementsActionInterface(): void
    {
        $rc = new ReflectionClass(CreateChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementsApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CreateChargeAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowNotSupportedApiIfNotKeysGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new CreateChargeAction();

        $action->setApi('not keys instance');
    }

    public function testShouldSupportCreateChargeRequestWithArrayAccessModel(): void
    {
        $action = new CreateChargeAction();

        $this->assertTrue($action->supports(new CreateCharge([])));
    }

    public function testShouldNotSupportCreateChargeRequestWithNotArrayAccessModel(): void
    {
        $action = new CreateChargeAction();

        $this->assertFalse($action->supports(new CreateCharge(new stdClass())));
    }

    public function testShouldNotSupportNotCreateChargeRequest(): void
    {
        $action = new CreateChargeAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowRequestNotSupportedIfNotSupportedGiven(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreateChargeAction is not supported the request stdClass.');
        $action = new CreateChargeAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfNotCardNorCustomerSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The either card token or customer id has to be set.');
        $action = new CreateChargeAction();

        $action->execute(new CreateCharge([]));
    }

    public function testThrowIfCardAlreadyUsed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The token has already been used.');
        $action = new CreateChargeAction();

        $action->execute(new CreateCharge([
            'card' => ['foo'],
        ]));
    }
}
