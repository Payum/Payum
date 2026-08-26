<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Model;

use Money\Money;
use Payum\Core\Model\BankAccountInterface;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\CreditCardPaymentInterface;
use Payum\Core\Model\DirectDebitPaymentInterface;
use Payum\Core\Model\MoneyAwareInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PaymentTest extends TestCase
{
    public function testShouldImplementsPaymentInterface(): void
    {
        $rc = new ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(PaymentInterface::class));
    }

    public function testShouldImplementsCreditCardPaymentInterface(): void
    {
        $rc = new ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(CreditCardPaymentInterface::class));
    }

    public function testShouldImplementsDirectDebitPaymentInterface(): void
    {
        $rc = new ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(DirectDebitPaymentInterface::class));
    }

    public function testShouldAllowGetCreditCardPreviouslySet(): void
    {
        $order = new Payment();

        $creditCardMock = $this->createMock(CreditCardInterface::class);

        $order->setCreditCard($creditCardMock);

        $this->assertSame($creditCardMock, $order->getCreditCard());
    }

    public function testShouldAllowGetBankAccountPreviouslySet(): void
    {
        $order = new Payment();

        $bankAccountMock = $this->createMock(BankAccountInterface::class);

        $order->setBankAccount($bankAccountMock);

        $this->assertSame($bankAccountMock, $order->getBankAccount());
    }

    public function testShouldImplementsMoneyAwareInterface(): void
    {
        $rc = new ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(MoneyAwareInterface::class));
    }

    public function testShouldAllowGetMoneyBuiltFromTheAmountAndCurrencyPreviouslySet(): void
    {
        $payment = new Payment();
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');

        $this->assertTrue(Money::USD(123)->equals($payment->getMoney()));
    }

    public function testShouldWriteAMoneyThroughToTheAmountAndCurrency(): void
    {
        $payment = new Payment();

        $payment->setMoney(Money::KWD(1234));

        $this->assertSame(1234, $payment->getTotalAmount());
        $this->assertSame('KWD', $payment->getCurrencyCode());
    }

    public function testShouldClearTheAmountAndCurrencyTogether(): void
    {
        $payment = new Payment();
        $payment->setMoney(Money::USD(123));

        $payment->setMoney(null);

        $this->assertNull($payment->getTotalAmount());
        $this->assertNull($payment->getCurrencyCode());
    }
}
