<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;

class CreateButtonPaymentActionTest extends \PHPUnit\Framework\TestCase
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
    public function shouldSupportCreateButtonPaymentRequestAndArrayAccessAsModel()
    {
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment($this->createMock('ArrayAccess'));

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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateButtonPaymentAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfModelNotHavePaymentAmountOrCurrencySet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment(new \ArrayObject());

        $action->execute($request);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Paypal\ProHosted\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ProHosted\Nvp\Api', array(), array(), '', false);
    }
}
