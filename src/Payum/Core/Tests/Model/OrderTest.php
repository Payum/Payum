<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Order;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsOrderInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\Order');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\OrderInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new Order();
    }

    /**
     * @test
     */
    public function shouldAllowGetCreditCardPreviouslySet()
    {
        $order = new Order();

        $creditCardMock = $this->getMock('Payum\Core\Model\CreditCardInterface');

        $order->setCreditCard($creditCardMock);

        $this->assertSame($creditCardMock, $order->getCreditCard());
    }
}
