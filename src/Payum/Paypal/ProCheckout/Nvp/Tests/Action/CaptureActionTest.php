<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Core\Request\ObtainCreditCard;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = CaptureAction::class;

    protected $requestClass = Capture::class;

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
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
            'AMOUNT' => 10,
        ));

        //guard
        $this->assertTrue($action->supports($request));

        $action->execute($request);
    }

    public function testShouldDoNothingIfResultSet()
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

        $request = new Capture(array('RESULT' => Api::RESULT_SUCCESS));

        $action->execute($request);
    }

    public function testShouldCaptureWithCreditCardSetExplicitly()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $result = array('FOO' => 'FOOVAL', 'BAR' => 'BARVAL');

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doSale')
            ->willReturn($result)
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'AMOUNT' => 10,
            'ACCT' => '1234432112344321',
            'CVV2' => 123,
            'EXPDATE' => '1016',
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

        $result = array('FOO' => 'FOOVAL', 'BAR' => 'BARVAL');

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doSale')
            ->willReturn($result)
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new Capture(array(
            'AMOUNT' => 10,
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

    public function testShouldPassFirstAndCurrentModelsWithObtainCreditCardSubRequest()
    {
        $firstModel = new \stdClass();
        $currentModel = new \ArrayObject(array(
            'AMOUNT' => 10,
        ));

        $result = array('FOO' => 'FOOVAL', 'BAR' => 'BARVAL');

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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ProCheckout\Nvp\Api', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
