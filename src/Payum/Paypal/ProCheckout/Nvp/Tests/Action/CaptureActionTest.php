<?php

namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use ArrayObject;
use DateTime;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\CreditCard;
use Payum\Core\Request\Capture;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testThrowIfUnsupportedApiGiven(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new CaptureAction();

        $action->setApi(new stdClass());
    }

    public function testThrowIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ObtainCreditCard::class))
            ->willThrowException(new RequestNotSupportedException())
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'AMOUNT' => 10,
        ]);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);
    }

    public function testShouldDoNothingIfResultSet(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('doSale')
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'RESULT' => Api::RESULT_SUCCESS,
        ]);

        $action->execute($request);
    }

    public function testShouldCaptureWithCreditCardSetExplicitly(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $result = [
            'FOO' => 'FOOVAL',
            'BAR' => 'BARVAL',
        ];

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doSale')
            ->willReturn($result)
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'AMOUNT' => 10,
            'ACCT' => '1234432112344321',
            'CVV2' => 123,
            'EXPDATE' => '1016',
        ]);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertArrayHasKey('AMOUNT', $model);
        $this->assertSame(10, $model['AMOUNT']);

        $this->assertArrayHasKey('FOO', $model);
        $this->assertSame('FOOVAL', $model['FOO']);
    }

    public function testShouldCaptureWithObtainedCreditCard(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(ObtainCreditCard::class))
            ->willReturnCallback(function (ObtainCreditCard $request): void {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new DateTime('2014-10-01'));
                $card->setHolder('John Doe');
                $card->setSecurityCode('123');

                $request->set($card);
            })
        ;

        $result = [
            'FOO' => 'FOOVAL',
            'BAR' => 'BARVAL',
        ];

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doSale')
            ->willReturn($result)
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture([
            'AMOUNT' => 10,
        ]);

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertArrayHasKey('AMOUNT', $model);
        $this->assertSame(10, $model['AMOUNT']);

        $this->assertArrayHasKey('FOO', $model);
        $this->assertSame('FOOVAL', $model['FOO']);
    }

    public function testShouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest(): void
    {
        $firstModel = new stdClass();
        $currentModel = new ArrayObject([
            'AMOUNT' => 10,
        ]);

        $result = [
            'FOO' => 'FOOVAL',
            'BAR' => 'BARVAL',
        ];

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doSale')
            ->willReturn($result)
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
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $capture = new Capture($firstModel);
        $capture->setModel($currentModel);

        $action->execute($capture);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
