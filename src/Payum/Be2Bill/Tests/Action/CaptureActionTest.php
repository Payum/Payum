<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Be2Bill\Action\CaptureAction;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Tests\GenericActionTest;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Be2Bill\Action\CaptureAction';

    protected $requestClass = 'Payum\Core\Request\Capture';

    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Action\CaptureAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

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
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.
     */
    public function throwIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture()
    {
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
            ->method('gateway')
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Be2Bill\Api', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
