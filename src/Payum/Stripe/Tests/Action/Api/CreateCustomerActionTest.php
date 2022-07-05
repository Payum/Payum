<?php

namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateCustomerAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCustomer;

class CreateCustomerActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateCustomerAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CreateCustomerAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCreateCustomerRequestWithArrayAccessModel()
    {
        $action = new CreateCustomerAction();

        $this->assertTrue($action->supports(new CreateCustomer([])));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreateCustomerRequestWithNotArrayAccessModel()
    {
        $action = new CreateCustomerAction();

        $this->assertFalse($action->supports(new CreateCustomer(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCreateCustomerRequest()
    {
        $action = new CreateCustomerAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $this->expectExceptionMessage('Action CreateCustomerAction is not supported the request stdClass.');
        $action = new CreateCustomerAction();

        $action->execute(new \stdClass());
    }
}
