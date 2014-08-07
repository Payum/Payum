<?php
namespace Payum\Stripe\Tests\Action\Api;

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
        $rc = new \ReflectionClass('Payum\Stripe\Action\Api\CreateChargeAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\Api\CreateChargeAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateChargeAction;
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CreateChargeAction;

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CreateChargeAction;

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCreateChargeRequestWithArrayAccessModel()
    {
        $action = new CreateChargeAction;

        $this->assertTrue($action->supports(new CreateCharge(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreateChargeRequestWithNotArrayAccessModel()
    {
        $action = new CreateChargeAction;

        $this->assertFalse($action->supports(new CreateCharge(new \stdClass)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCreateChargeRequest()
    {
        $action = new CreateChargeAction;

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreateChargeAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreateChargeAction;

        $action->execute(new \stdClass);
    }
} 