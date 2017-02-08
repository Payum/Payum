<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
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

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldDoNothingIfPaymentHasStatus()
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

    /**
     * @test
     */
    public function shouldSubExecuteObtainTokenRequestIfTokenNotSet()
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

    /**
     * @test
     */
    public function shouldSubExecuteObtainTokenRequestWithCurrentModel()
    {
        $model = new \ArrayObject(['foo' => 'fooVal']);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->will($this->returnCallback(function (ObtainToken $request) use ($model) {
                $this->assertInstanceOf(ArrayObject::class, $request->getModel());
                $this->assertSame(['foo' => 'fooVal'], (array) $request->getModel());
            }))
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Capture($model));
    }

    /**
     * @test
     */
    public function shouldSubExecuteCreateChargeIfTokenSetButNotUsed()
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

    /**
     * @test
     */
    public function shouldNotSubExecuteCreateChargeIfAlreadyCharged()
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

    /**
     * @test
     */
    public function shouldSubExecuteCreateChargeIfCustomerSet()
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

    /**
     * @test
     */
    public function shouldSubExecuteCreateChargeIfCreditCardSetExplisitly()
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

    /**
     * @test
     */
    public function shouldNotSubExecuteCreateChargeIfCustomerSetButAlreadyCharged()
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
        return $this->getMock(GatewayInterface::class);
    }
}
