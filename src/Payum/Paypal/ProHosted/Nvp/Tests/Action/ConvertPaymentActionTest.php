<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProHosted\Nvp\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Paypal\ProHosted\Nvp\Action\ConvertPaymentAction';

    protected $requestClass = 'Payum\Core\Request\Convert';

    public function provideSupportedRequests(): \Iterator
    {
        yield [new $this->requestClass(new Payment(), 'array')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'array')];
        yield [new $this->requestClass(new Payment(), 'array', $this->createMock('Payum\Core\Security\TokenInterface'))];
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
        yield [new $this->requestClass(new \stdClass(), 'array')];
        yield [new $this->requestClass(new Payment(), 'foobar')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')];
    }

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock->expects($this->once())->method('execute')->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))->willReturnCallback(function (GetCurrency $request) {
            $request->name = 'US Dollar';
            $request->alpha3 = 'USD';
            $request->numeric = 123;
            $request->exp = 2;
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
        $this->assertSame('theNumber', $details['INVNUM']);

        $this->assertArrayHasKey('AMT', $details);
        $this->assertSame(123.0, $details['AMT']);

        $this->assertArrayHasKey('CURRENCYCODE', $details);
        $this->assertSame('USD', $details['CURRENCYCODE']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock->expects($this->once())->method('execute')->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))->willReturnCallback(function (GetCurrency $request) {
            $request->name = 'US Dollar';
            $request->alpha3 = 'USD';
            $request->numeric = 123;
            $request->exp = 2;
            $request->country = 'US';
        });

        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails([
            'foo' => 'fooVal',
        ]);

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
