<?php

namespace Payum\Paypal\Masspay\Nvp\Tests\Action;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Payout;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Masspay\Nvp\Action\PayoutAction;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class PayoutActionTest extends GenericActionTest
{
    protected $requestClass = Payout::class;

    protected $actionClass = PayoutAction::class;

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldDoMasspayRequestIfModelNotAcknowledge(): void
    {
        $payoutModel = new ArrayObject([
            'bar' => 'barVal',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Masspay::class))
            ->willReturnCallback(function (Masspay $request): void {
                $model = $request->getModel();

                $model['foo'] = 'fooVal';
            })
        ;

        $action = new PayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Payout($payoutModel));

        $this->assertSame([
            'bar' => 'barVal',
            'foo' => 'fooVal',
        ], (array) $payoutModel);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
