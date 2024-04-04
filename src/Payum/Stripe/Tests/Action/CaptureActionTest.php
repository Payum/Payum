<?php
namespace Payum\Stripe\Tests\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\CaptureAction;
use Payum\Stripe\Constants;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldDoNothingIfPaymentHasStatus()
    {
        $model = [
            'status' => Constants::STATUS_SUCCEEDED,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldSubExecuteObtainTokenRequestIfTokenNotSet()
    {
        $model = array();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(ObtainToken::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldSubExecuteObtainTokenRequestWithCurrentModel()
    {
        $model = new \ArrayObject(['foo' => 'fooVal']);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->willReturnCallback(function (ObtainToken $request) use ($model) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $this->assertSame(['foo' => 'fooVal'], (array) $request->getModel());
            })
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldSubExecuteCreateChargeIfTokenSetButNotUsed()
    {
        $model = array(
            'card' => 'notUsedToken',
        );

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCharge::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldNotSubExecuteCreateChargeIfAlreadyCharged()
    {
        $model = [
            'card' => 'theToken',
            'status' => Constants::STATUS_PAID,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldSubExecuteCreateChargeIfCustomerSet()
    {
        $model = [
            'customer' => 'theCustomerId',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCharge::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldSubExecuteCreateChargeIfCreditCardSetExplisitly()
    {
        $model = [
            'card' => [
                'number' => '4111111111111111',
                'exp_month' => '10',
                'exp_year' => '20',
                'cvc' => '123',
            ],
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(CreateCharge::class))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    public function testShouldNotSubExecuteCreateChargeIfCustomerSetButAlreadyCharged()
    {
        $model = [
            'customer' => 'theCustomerId',
            'status' => Constants::STATUS_SUCCEEDED,
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
