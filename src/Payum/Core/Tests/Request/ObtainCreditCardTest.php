<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\ObtainCreditCard;

class ObtainCreditCardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGenericRequest()
    {
        $rc = new \ReflectionClass(ObtainCreditCard::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        $request = new ObtainCreditCard();

        $this->assertNull($request->getFirstModel());
        $this->assertNull($request->getModel());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithFirstModelAsFirstArgument()
    {
        $request = new ObtainCreditCard($firstModel = new \stdClass());

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertNull($request->getModel());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithFirstModelAndCurrentModelAsArguments()
    {
        $request = new ObtainCreditCard($firstModel = new \stdClass(), $currentModel = new \stdClass());

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($currentModel, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowSetCreditCard()
    {
        $request = new ObtainCreditCard();

        $request->set($this->getMock(CreditCardInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowObtainPreviouslySetCreditCard()
    {
        $request = new ObtainCreditCard();

        $card = $this->getMock(CreditCardInterface::class);

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
