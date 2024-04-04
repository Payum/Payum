<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Tests\GenericActionTest;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\CaptureAction';

    protected $requestClass = 'Payum\Core\Request\Capture';

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Action\CaptureAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    public function testShouldAllowSetApi()
    {
        $this->assertInstanceOf(ApiAwareInterface::class, new CaptureAction());
    }

    /**
     * @group legacy
     */
    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldDoNothingIfResponseCodeSet()
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

    public function testShouldCaptureWithCreditCardSetExplicitly()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->willReturn($this->createAuthorizeNetAIMResponseMock())
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

    public function testThrowIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->never())
            ->method('authorizeAndCapture')
            ->willReturn($this->createAuthorizeNetAIMResponseMock())
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->willThrowException(new RequestNotSupportedException())
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture(array(
            'amount' => 10,
        )));
    }

    public function testShouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest()
    {
        $firstModel = new \stdClass();
        $currentModel = new \ArrayObject(array(
            'amount' => 10,
        ));

        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->willReturn($this->createAuthorizeNetAIMResponseMock())
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->willReturnCallback(function (ObtainCreditCard $request) use ($firstModel, $currentModel) {
                $this->assertSame($firstModel, $request->getFirstModel());
                $this->assertSame($currentModel, $request->getModel());

                $card = new CreditCard();
                $card->setExpireAt(new \DateTime('2014-10-01'));

                $request->set($card);
            })
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $capture = new Capture($firstModel);
        $capture->setModel($currentModel);

        $action->execute($capture);
    }

    public function testShouldCaptureWithObtainedCreditCard()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->willReturn($this->createAuthorizeNetAIMResponseMock())
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->willReturnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new \DateTime('2014-10-01'));

                $request->set($card);
            })
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
        return $this->createMock('Payum\Core\GatewayInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthorizeNetAIM
     */
    protected function createAuthorizeNetAIMMock()
    {
        return $this->createMock('Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AuthorizeNetAIM_Response
     */
    protected function createAuthorizeNetAIMResponseMock()
    {
        return $this->createMock('AuthorizeNetAIM_Response', array(), array(), '', false);
    }
}
