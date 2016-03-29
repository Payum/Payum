<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Tests\SkipOnPhp7Trait;

class CaptureActionTest extends GenericActionTest
{
    use SkipOnPhp7Trait;

    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\CaptureAction';

    protected $requestClass = 'Payum\Core\Request\Capture';

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
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Action\CaptureAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createAuthorizeNetAIMMock();

        $action = new CaptureAction();
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfResponseCodeSet()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->never())
            ->method('authorizeAndCapture')
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'response_code' => 'foo',
        )));
    }

    /**
     * @test
     */
    public function shouldCaptureWithCreditCardSetExplicitly()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'amount' => 10,
            'card_num' => '1234567812345678',
            'exp_date' => '10/16',
        )));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.
     */
    public function throwIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->never())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->throwException(new RequestNotSupportedException()))
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'amount' => 10,
        )));
    }

    /**
     * @test
     */
    public function shouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest()
    {
        $firstModel = new \stdClass();
        $currentModel = new \ArrayObject(array(
            'amount' => 10,
        ));

        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->returnCallback(function (ObtainCreditCard $request) use ($firstModel, $currentModel) {
                $this->assertSame($firstModel, $request->getFirstModel());
                $this->assertSame($currentModel, $request->getModel());

                $card = new CreditCard();
                $card->setExpireAt(new \DateTime('2014-10-01'));

                $request->set($card);
            }))
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $capture = new Capture($firstModel);
        $capture->setModel($currentModel);

        $action->execute($capture);
    }

    /**
     * @test
     */
    public function shouldCaptureWithObtainedCreditCard()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->returnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new \DateTime('2014-10-01'));

                $request->set($card);
            }))
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'amount' => 10,
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthorizeNetAIM
     */
    protected function createAuthorizeNetAIMMock()
    {
        return $this->getMock('Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AuthorizeNetAIM_Response
     */
    protected function createAuthorizeNetAIMResponseMock()
    {
        return $this->getMock('AuthorizeNetAIM_Response', array(), array(), '', false);
    }
}
