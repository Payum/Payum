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
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function throwIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->throwException(new RequestNotSupportedException()))
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

    /**
     * @test
     */
    public function shouldDoNothingIfExeccodeSet()
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

    /**
     * @test
     */
    public function shouldCaptureWithCreditCardSetExplicitly()
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
            ->will($this->returnValue(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            )))
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
        $this->assertEquals(10, $model['AMOUNT']);

        $this->assertArrayHasKey('FOO', $model);
        $this->assertEquals('FOOVAL', $model['FOO']);
    }

    /**
     * @test
     */
    public function shouldCaptureWithObtainedCreditCard()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->returnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new \DateTime('2014-10-01'));
                $card->setHolder('John Doe');
                $card->setSecurityCode('123');

                $request->set($card);
            }))
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('payment')
            ->will($this->returnValue(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            )))
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
        $this->assertEquals(10, $model['AMOUNT']);

        $this->assertArrayHasKey('FOO', $model);
        $this->assertEquals('FOOVAL', $model['FOO']);

        $this->assertArrayHasKey('CARDCODE', $model);
        $this->assertInstanceOf(SensitiveValue::class, $model['CARDCODE']);
        $this->assertNull($model['CARDCODE']->peek(), 'Already erased');
    }

    /**
     * @test
     */
    public function shouldCaptureWithObtainedCreditCardWhenTokenReturned()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->returnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setToken('theCreditCardToken');

                $request->set($card);
            }))
        ;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('payment')
            ->will($this->returnValue(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            )))
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

        $this->assertEquals([
            'AMOUNT' => 10,
            'CLIENTUSERAGENT' => 'anAgent',
            'CLIENTIP' => '127.0.0.1',
            'ALIAS' => 'theCreditCardToken',
            'FOO' => 'FOOVAL',
            'BAR' => 'BARVAL',
        ], $model);
    }

    /**
     * @test
     */
    public function shouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest()
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
            ->will($this->returnValue(array(
                'FOO' => 'FOOVAL',
                'BAR' => 'BARVAL',
            )))
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
