<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProHosted\Nvp\Action\CaptureAction;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\CreateButtonPayment;
use Payum\Paypal\ProHosted\Nvp\Action\Api\CreateButtonPaymentAction;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldRequestApiCreateButtonPaymentMethodWithExpectedRequiredArguments()
    {
        $gatewayMock = $this->createGatewayMock();

        $gatewayMock->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class));

        $gatewayMock->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(CreateButtonPayment::class));

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([
            'currency_code' => 'EUR',
            'subtotal'      => 5,
        ]));
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
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
