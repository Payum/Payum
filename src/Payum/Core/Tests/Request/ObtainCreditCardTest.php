<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\ObtainCreditCard;

class ObtainCreditCardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ObtainCreditCard();
    }

    /**
     * @test
     */
    public function shouldAllowSetCreditCard()
    {
        $request = new ObtainCreditCard();

        $request->set($this->getMock('Payum\Core\Model\CreditCardInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowObtainPreviouslySetCreditCard()
    {
        $request = new ObtainCreditCard();

        $card = $this->getMock('Payum\Core\Model\CreditCardInterface');

        $request->set($card);

        $this->assertSame($card, $request->obtain());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Credit card could not be obtained. It has to be set before obtain.
     */
    public function throwIfObtainCalledBeforeCreditCardSet()
    {
        $request = new ObtainCreditCard();

        $request->obtain();
    }
}
