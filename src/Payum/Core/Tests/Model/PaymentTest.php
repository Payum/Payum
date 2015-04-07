<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsPaymentInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\Payment');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\PaymentInterface'));
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

        $creditCardMock = $this->getMock('Payum\Core\Model\CreditCardInterface');

        $order->setCreditCard($creditCardMock);

        $this->assertSame($creditCardMock, $order->getCreditCard());
    }
}
