<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateCustomerAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCustomer;

class CreateCustomerActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowNotSupportedApiIfNotKeysGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CreateCustomerAction();

        $action->setApi('not keys instance');
    }

    public function testShouldSupportCreateCustomerRequestWithArrayAccessModel()
    {
        $action = new CreateCustomerAction();

        $this->assertTrue($action->supports(new CreateCustomer([])));
    }

    public function testShouldNotSupportCreateCustomerRequestWithNotArrayAccessModel()
    {
        $action = new CreateCustomerAction();

        $this->assertFalse($action->supports(new CreateCustomer(new \stdClass())));
    }

    public function testShouldNotSupportNotCreateCustomerRequest()
    {
        $action = new CreateCustomerAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowRequestNotSupportedIfNotSupportedGiven()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreateCustomerAction is not supported the request stdClass.');
        $action = new CreateCustomerAction();

        $action->execute(new \stdClass());
    }
}
