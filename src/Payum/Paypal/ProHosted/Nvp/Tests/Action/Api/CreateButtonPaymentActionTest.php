<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use ArrayAccess;
use ArrayObject;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateButtonPaymentActionTest extends TestCase
{
    public function testShouldImplementsApiAwareAction()
    {
        $rc = new ReflectionClass(CreateButtonPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportCreateButtonPaymentRequestAndArrayAccessAsModel()
    {
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment($this->createMock(ArrayAccess::class));

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotCreateButtonPaymentRequest()
    {
        $action = new CreateButtonPaymentAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreateButtonPaymentAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfModelNotHavePaymentAmountOrCurrencySet()
    {
        $this->expectException(LogicException::class);
        $action = new CreateButtonPaymentAction();

        $request = new CreateButtonPayment(new ArrayObject());

        $action->execute($request);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
