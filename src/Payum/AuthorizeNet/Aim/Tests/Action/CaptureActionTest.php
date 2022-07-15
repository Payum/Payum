<?php

namespace Payum\AuthorizeNet\Aim\Tests\Action;

use ArrayObject;
use AuthorizeNetAIM_Response;
use DateTime;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\CreditCard;
use Payum\Core\Request\Capture;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

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

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldAllowSetApi(): void
    {
        $this->assertInstanceOf(ApiAwareInterface::class, new CaptureAction());
    }

    /**
     * @group legacy
     */
    public function testThrowIfUnsupportedApiGiven(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new CaptureAction();

        $action->setApi(new stdClass());
    }

    public function testShouldDoNothingIfResponseCodeSet(): void
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

        $action->execute(new Capture([
            'response_code' => 'foo',
        ]));
    }

    public function testShouldCaptureWithCreditCardSetExplicitly(): void
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

        $action->execute(new Capture([
            'amount' => 10,
            'card_num' => '1234567812345678',
            'exp_date' => '10/16',
        ]));
    }

    public function testThrowIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture(): void
    {
        $this->expectException(LogicException::class);
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
            ->with($this->isInstanceOf(ObtainCreditCard::class))
            ->willThrowException(new RequestNotSupportedException())
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([
            'amount' => 10,
        ]));
    }

    public function testShouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest(): void
    {
        $firstModel = new stdClass();
        $currentModel = new ArrayObject([
            'amount' => 10,
        ]);

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
            ->with($this->isInstanceOf(ObtainCreditCard::class))
            ->willReturnCallback(function (ObtainCreditCard $request) use ($firstModel, $currentModel): void {
                $this->assertSame($firstModel, $request->getFirstModel());
                $this->assertSame($currentModel, $request->getModel());

                $card = new CreditCard();
                $card->setExpireAt(new DateTime('2014-10-01'));

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

    public function testShouldCaptureWithObtainedCreditCard(): void
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
            ->with($this->isInstanceOf(ObtainCreditCard::class))
            ->willReturnCallback(function (ObtainCreditCard $request): void {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new DateTime('2014-10-01'));

                $request->set($card);
            })
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gatewayMock);

        $action->execute(new Capture([
            'amount' => 10,
        ]));
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }

    /**
     * @return MockObject|AuthorizeNetAIM
     */
    protected function createAuthorizeNetAIMMock()
    {
        return $this->createMock(AuthorizeNetAIM::class);
    }

    /**
     * @return MockObject|AuthorizeNetAIM_Response
     */
    protected function createAuthorizeNetAIMResponseMock()
    {
        return $this->createMock(AuthorizeNetAIM_Response::class);
    }
}
