<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;

class CreateButtonPaymentActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsApiAwareAction()
    {
        $rc = new \ReflectionClass(CreateButtonPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportCreateButtonPaymentRequestAndArrayAccessAsModel()
    {
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotCreateButtonPaymentRequest()
    {
        $action = new CreateButtonPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateButtonPaymentAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfModelNotHavePaymentAmountOrCurrencySet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment(new \ArrayObject());

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ProHosted\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ProHosted\Nvp\Api', array(), array(), '', false);
    }
}
