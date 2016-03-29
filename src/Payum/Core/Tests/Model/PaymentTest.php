<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\BankAccountInterface;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\CreditCardPaymentInterface;
use Payum\Core\Model\DirectDebitPaymentInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsPaymentInterface()
    {
        $rc = new \ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(PaymentInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsCreditCardPaymentInterface()
    {
        $rc = new \ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(CreditCardPaymentInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementsDirectDebitPaymentInterface()
    {
        $rc = new \ReflectionClass(Payment::class);

        $this->assertTrue($rc->implementsInterface(DirectDebitPaymentInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Payment();
    }

    /**
     * @test
     */
    public function shouldAllowGetCreditCardPreviouslySet()
    {
        $order = new Payment();

        $creditCardMock = $this->getMock(CreditCardInterface::class);

        $order->setCreditCard($creditCardMock);

        $this->assertSame($creditCardMock, $order->getCreditCard());
    }

    /**
     * @test
     */
    public function shouldAllowGetBankAccountPreviouslySet()
    {
        $order = new Payment();

        $bankAccountMock = $this->getMock(BankAccountInterface::class);

        $order->setBankAccount($bankAccountMock);

        $this->assertSame($bankAccountMock, $order->getBankAccount());
    }
}
