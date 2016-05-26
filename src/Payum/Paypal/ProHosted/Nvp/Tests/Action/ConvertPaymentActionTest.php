<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProHosted\Nvp\Action\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Paypal\ProHosted\Nvp\Action\ConvertPaymentAction';

    protected $requestClass = 'Payum\Core\Request\Convert';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment(), 'array')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'array')),
            array(new $this->requestClass(new Payment(), 'array', $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payment(), 'foobar')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $gatewayMock = $this->getMock('Payum\Core\GatewayInterface');
        $gatewayMock->expects($this->once())->method('execute')->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))->willReturnCallback(function (GetCurrency $request) {
            $request->name    = 'US Dollar';
            $request->alpha3  = 'USD';
            $request->numeric = 123;
            $request->exp     = 2;
            $request->country = 'US';
        });

        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('INVNUM', $details);
        $this->assertEquals('theNumber', $details['INVNUM']);

        $this->assertArrayHasKey('AMT', $details);
        $this->assertEquals(123, $details['AMT']);

        $this->assertArrayHasKey('CURRENCYCODE', $details);
        $this->assertEquals('USD', $details['CURRENCYCODE']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->getMock('Payum\Core\GatewayInterface');
        $gatewayMock->expects($this->once())->method('execute')->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))->willReturnCallback(function (GetCurrency $request) {
            $request->name    = 'US Dollar';
            $request->alpha3  = 'USD';
            $request->numeric = 123;
            $request->exp     = 2;
            $request->country = 'US';
        });

        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
