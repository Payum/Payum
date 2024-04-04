<?php
namespace Payum\Paypal\Masspay\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Payout;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Masspay\Nvp\Action\PayoutAction;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;

class PayoutActionTest extends GenericActionTest
{
    protected $requestClass = Payout::class;

    protected $actionClass = PayoutAction::class;

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldDoMasspayRequestIfModelNotAcknowledge()
    {
        $payoutModel = new \ArrayObject([
            'bar' => 'barVal',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Masspay::class))
            ->willReturnCallback(function (Masspay $request) {
                $model = $request->getModel();

                $model['foo'] = 'fooVal';
            })
        ;

        $action = new PayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Payout($payoutModel));

        $this->assertSame(['bar' => 'barVal', 'foo' => 'fooVal'], (array) $payoutModel);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
