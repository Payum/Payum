<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateCustomerAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCustomer;

class CreateCustomerActionTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateCustomerAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CreateCustomerAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreateCustomerAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreateCustomerAction();

        $action->execute(new \stdClass());
    }
}
