<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Stripe\Action\Api\CreateRefundAction;
use Payum\Stripe\Keys;
use Payum\Core\Request\Refund;

class CreateRefundActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(CreateRefundAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(CreateRefundAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateRefundAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new CreateRefundAction();

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new CreateRefundAction();

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportCreateRefundRequestWithArrayAccessModel()
    {
        $action = new CreateRefundAction();

        $this->assertTrue($action->supports(new Refund(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreateRefundRequestWithNotArrayAccessModel()
    {
        $action = new CreateRefundAction();

        $this->assertFalse($action->supports(new Refund(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotCreateRefundRequest()
    {
        $action = new CreateRefundAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action CreateRefundAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new CreateRefundAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The charge id has to be set
     */
    public function throwIfNoChargeIdIsSet()
    {
        $action = new CreateRefundAction();

        $action->execute(new Refund([]));
    }
}
