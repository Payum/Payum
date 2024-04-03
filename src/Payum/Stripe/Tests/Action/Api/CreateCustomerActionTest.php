<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Action\Api\CreateCustomerAction;
use Payum\Stripe\Request\Api\CreateCustomer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateCustomerActionTest extends TestCase
{
    public function testShouldImplementsActionInterface(): void
    {
        $rc = new ReflectionClass(CreateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementsApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CreateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowNotSupportedApiIfNotKeysGivenAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new CreateCustomerAction();

        $action->setApi('not keys instance');
    }

    public function testShouldSupportCreateCustomerRequestWithArrayAccessModel(): void
    {
        $action = new CreateCustomerAction();

        $this->assertTrue($action->supports(new CreateCustomer([])));
    }

    public function testShouldNotSupportCreateCustomerRequestWithNotArrayAccessModel(): void
    {
        $action = new CreateCustomerAction();

        $this->assertFalse($action->supports(new CreateCustomer(new stdClass())));
    }

    public function testShouldNotSupportNotCreateCustomerRequest(): void
    {
        $action = new CreateCustomerAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowRequestNotSupportedIfNotSupportedGiven(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreateCustomerAction is not supported the request stdClass.');
        $action = new CreateCustomerAction();

        $action->execute(new stdClass());
    }
}
