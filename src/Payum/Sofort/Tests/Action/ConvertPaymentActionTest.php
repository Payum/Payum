<?php

declare(strict_types=1);

namespace Payum\Sofort\Tests\Action;

use Iterator;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Sofort\Action\ConvertPaymentAction;
use stdClass;

final class ConvertPaymentActionTest extends GenericActionTest
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
    }

    public function testShouldCorrectlyConvertPaymentToArray(): void
    {
        $payment = new Payment();
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');

        $result = $this->convert($payment, 2);

        $this->assertSame('EUR', $result['currency_code']);
        $this->assertSame('1.23', $result['amount']);
        $this->assertSame('the description', $result['reason']);
    }

    public function testShouldConvertACurrencyWithNoMinorUnits(): void
    {
        $payment = new Payment();
        $payment->setCurrencyCode('JPY');
        $payment->setTotalAmount(123);

        $this->assertSame('123', $this->convert($payment, 0)['amount']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails(): void
    {
        $payment = new Payment();
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setDetails([
            'foo' => 'fooVal',
        ]);

        $this->assertSame('fooVal', $this->convert($payment, 2)['foo']);
    }

    /**
     * @return array<string, mixed>
     */
    private function convert(Payment $payment, int $exp): array
    {
        $gateway = $this->createMock(GatewayInterface::class);
        $gateway
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetCurrency::class))
            ->willReturnCallback(function (GetCurrency $request) use ($payment, $exp): void {
                $request->alpha3 = $payment->getCurrencyCode();
                $request->exp = $exp;
            })
        ;

        $action = new ConvertPaymentAction();
        $action->setGateway($gateway);

        $action->execute($convert = new Convert($payment, 'array'));

        return $convert->getResult();
    }
}
