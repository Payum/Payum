<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;

class CreateButtonPaymentActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsApiAwareAction()
    {
        $rc = new \ReflectionClass(CreateButtonPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateButtonPaymentAction();
    }

    /**
     * @test
     */
    public function shouldSupportCreateButtonPaymentRequestAndArrayAccessAsModel()
    {
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment($this->getMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreateButtonPaymentRequest()
    {
        $action = new CreateButtonPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CreateButtonPaymentAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     */
    public function throwIfModelNotHavePaymentAmountOrCurrencySet()
    {
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment(new \ArrayObject());

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ProHosted\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ProHosted\Nvp\Api', array(), array(), '', false);
    }
}
