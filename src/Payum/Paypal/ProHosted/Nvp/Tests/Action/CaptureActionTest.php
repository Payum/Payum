<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;
use Payum\Paypal\ProHosted\Nvp\Action\CaptureAction;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class CaptureActionTest extends GenericActionTest
{
    /**
     * @var class-string<Capture>
     */
    protected $requestClass = Capture::class;

    /**
     * @var class-string<CaptureAction>
     */
    protected $actionClass = CaptureAction::class;

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldRequestApiCreateButtonPaymentMethodWithExpectedRequiredArguments(): void
    {
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock->expects($this->atLeast(2))
            ->method('execute')
            ->withConsecutive(
                [$this->isInstanceOf(GetHttpRequest::class)],
                [$this->isInstanceOf(CreateButtonPayment::class)]
            )
        ;
        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([
            'currency_code' => 'EUR',
            'subtotal' => 5,
        ]));
    }

    public function testThrowIfModelNotHavePaymentAmountOrCurrencySet(): void
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

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
