<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Iterator;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\ConvertPaymentAction;
use stdClass;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPaymentAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass(new Payment(), 'array')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'array')];
        yield [new $this->requestClass(new Payment(), 'array', $this->createMock(TokenInterface::class))];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
        yield [new $this->requestClass(new stdClass(), 'array')];
        yield [new $this->requestClass(new Payment(), 'foobar')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')];
    }

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

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

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $details);
        $this->assertEqualsWithDelta(1.23, $details['PAYMENTREQUEST_0_AMT'], PHP_FLOAT_EPSILON);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_CURRENCYCODE', $details);
        $this->assertSame('USD', $details['PAYMENTREQUEST_0_CURRENCYCODE']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->createMock(GatewayInterface::class);
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

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
