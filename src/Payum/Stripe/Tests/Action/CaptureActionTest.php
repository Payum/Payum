<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\CaptureAction;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = 'Payum\Core\Request\Capture';

    protected $actionClass = 'Payum\Stripe\Action\CaptureAction';

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function shouldDoNothingIfModelHasAlreadyUsedToken()
    {
        $model = array(
            'card' => array('foo', 'bar'),
        );

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
    public function shouldSubExecuteObtainTokenReqeustIfTokenNotSet()
    {
        $model = array();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Stripe\Request\Api\ObtainToken'))
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
            ->with($this->isInstanceOf('Payum\Stripe\Request\Api\CreateCharge'))
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
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
