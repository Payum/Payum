<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsPaymentInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\Payment');

        $this->assertTrue($rc->implementsInterface(PaymentInterface::class));
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
}
