<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Model\Token;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Core\Model\Order;
use Payum\Core\Request\FillOrderDetails;

class FillOrderDetailsActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction';

    protected $requestClass = 'Payum\Core\Request\FillOrderDetails';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Order())),
            array(new $this->requestClass($this->getMock('Payum\Core\Model\OrderInterface'))),
            array(new $this->requestClass(new Order(), $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $order = new Order();
        $order->setNumber('theNumber');
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setClientId('theClientId');
        $order->setClientEmail('theClientEmail');

        $action = new FillOrderDetailsAction();

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('INVNUM', $details);
        $this->assertEquals('theNumber', $details['INVNUM']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $details);
        $this->assertEquals(1.23, $details['PAYMENTREQUEST_0_AMT']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_CURRENCYCODE', $details);
        $this->assertEquals('USD', $details['PAYMENTREQUEST_0_CURRENCYCODE']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $order = new Order();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new FillOrderDetailsAction();

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }

    /**
     * @test
     */
    public function shouldAddNotifyUrlIfTokenFactorySetAndCaptureTokenPassed()
    {
        $order = new Order();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $captureToken = new Token();
        $captureToken->setPaymentName('thePaymentName');
        $captureToken->setDetails($order);

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactoryMock = $this->getMock('Payum\Core\Security\GenericTokenFactoryInterface');
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with('thePaymentName', $this->identicalTo($order))
            ->will($this->returnValue($notifyToken))
        ;

        $action = new FillOrderDetailsAction($tokenFactoryMock);

        $action->execute(new FillOrderDetails($order, $captureToken));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertEquals('theNotifyUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);
    }

    /**
     * @test
     */
    public function shouldNotAddNotifyUrlIfAlreadySet()
    {
        $order = new Order();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'PAYMENTREQUEST_0_NOTIFYURL' => 'alreadySetUrl',
        ));

        $captureToken = new Token();
        $captureToken->setPaymentName('thePaymentName');
        $captureToken->setDetails($order);

        $tokenFactoryMock = $this->getMock('Payum\Core\Security\GenericTokenFactoryInterface');
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new FillOrderDetailsAction($tokenFactoryMock);

        $action->execute(new FillOrderDetails($order, $captureToken));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
        $this->assertEquals('alreadySetUrl', $details['PAYMENTREQUEST_0_NOTIFYURL']);
    }

    /**
     * @test
     */
    public function shouldNotAddNotifyUrlIfCaptureTokenNotSet()
    {
        $order = new Order();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array());

        $tokenFactoryMock = $this->getMock('Payum\Core\Security\GenericTokenFactoryInterface');
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createNotifyToken')
        ;

        $action = new FillOrderDetailsAction($tokenFactoryMock);

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayNotHasKey('PAYMENTREQUEST_0_NOTIFYURL', $details);
    }
}
