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

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(PayoutAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldDoMasspayRequestIfModelNotAcknowledge()
    {
        $payoutModel = new \ArrayObject([
            'bar' => 'barVal',
        ]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Masspay::class))
            ->will($this->returnCallback(function (Masspay $request) {
                $model = $request->getModel();

                $model['foo'] = 'fooVal';
            }))
        ;

        $action = new PayoutAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Payout($payoutModel));

        $this->assertEquals(['foo' => 'fooVal', 'bar' => 'barVal'], (array) $payoutModel);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
