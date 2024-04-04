<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\BankAccountInterface;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\CreditCardPaymentInterface;
use Payum\Core\Model\DirectDebitPaymentInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testShouldImplementsPaymentInterface()
    {
        $rc = new \ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(PaymentInterface::class));
    }

    public function testShouldImplementsCreditCardPaymentInterface()
    {
        $rc = new \ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(CreditCardPaymentInterface::class));
    }

    public function testShouldImplementsDirectDebitPaymentInterface()
    {
        $rc = new \ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(DirectDebitPaymentInterface::class));
    }

    public function testShouldAllowGetCreditCardPreviouslySet()
    {
        $order = new Payment();

        $creditCardMock = $this->createMock(CreditCardInterface::class);

        $order->setCreditCard($creditCardMock);

        $this->assertSame($creditCardMock, $order->getCreditCard());
    }

    public function testShouldAllowGetBankAccountPreviouslySet()
    {
        $order = new Payment();

        $bankAccountMock = $this->createMock(BankAccountInterface::class);

        $order->setBankAccount($bankAccountMock);

        $this->assertSame($bankAccountMock, $order->getBankAccount());
    }
}
