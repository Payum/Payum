<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Be2Bill\Action\CaptureAction;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\GenericActionTest;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

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
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }

    public function testThrowIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->willThrowException(new RequestNotSupportedException())
        ;

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
            'CARDCODE' => '1234432112344321',
        ));

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);
    }

    public function testShouldDoNothingIfExeccodeSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('payment')
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(array('EXECCODE' => 1));

        $action->execute($request);
    }

    public function testShouldCaptureWithCreditCardSetExplicitly()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('payment')
            ->willReturn(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            ))
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'AMOUNT' => 10,
            'CARDCODE' => '1234432112344321',
            'CARDCVV' => 123,
            'CARDFULLNAME' => 'Johh Doe',
            'CARDVALIDITYDATE' => '10-16',
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
        ));

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertArrayHasKey('AMOUNT', $model);
        $this->assertSame(10, $model['AMOUNT']);

        $this->assertArrayHasKey('FOO', $model);
        $this->assertSame('FOOVAL', $model['FOO']);
    }

    public function testShouldCaptureWithObtainedCreditCard()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->willReturnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new \DateTime('2014-10-01'));
                $card->setHolder('John Doe');
                $card->setSecurityCode('123');

                $request->set($card);
            })
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('payment')
            ->willReturn(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            ))
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'AMOUNT' => 10,
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
        ));

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertArrayHasKey('AMOUNT', $model);
        $this->assertSame(10, $model['AMOUNT']);

        $this->assertArrayHasKey('FOO', $model);
        $this->assertSame('FOOVAL', $model['FOO']);

        $this->assertArrayHasKey('CARDCODE', $model);
        $this->assertInstanceOf(SensitiveValue::class, $model['CARDCODE']);
        $this->assertNull($model['CARDCODE']->peek(), 'Already erased');
    }

    public function testShouldCaptureWithObtainedCreditCardWhenTokenReturned()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->willReturnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setToken('theCreditCardToken');

                $request->set($card);
            })
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('payment')
            ->willReturn(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            ))
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'AMOUNT' => 10,
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
        ));

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);

        $model = iterator_to_array($request->getModel());

        $this->assertSame([
            'AMOUNT' => 10,
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
            'ALIAS' => 'theCreditCardToken',
            'FOO' => 'FOOVAL',
            'BAR' => 'BARVAL',
        ], $model);
    }

    public function testShouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest()
    {
        $firstModel = new \stdClass();
        $currentModel = new \ArrayObject(array(
            'AMOUNT' => 10,
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
        ));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('payment')
            ->willReturn(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            ))
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
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $capture = new Capture($firstModel);
        $capture->setModel($currentModel);

        $action->execute($capture);
    }

    public function testShouldThrowHttpResponseIfExecCode3DSecureRequired()
    {
        $action = new CaptureAction();

        $this->expectException(HttpResponse::class);
        $action->execute($status = new Capture([
            '3DSECUREHTML' => base64_encode('<html>foo</html>'),
            'EXECCODE' => Api::EXECCODE_3DSECURE_IDENTIFICATION_REQUIRED,
        ]));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
