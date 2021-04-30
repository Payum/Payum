<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\ObtainCreditCard;
use PHPUnit\Framework\TestCase;

class ObtainCreditCardTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGenericRequest()
    {
        $rc = new \ReflectionClass(ObtainCreditCard::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithoutAnyArguments()
    {
        $request = new ObtainCreditCard();

        $this->assertNull($request->getFirstModel());
        $this->assertNull($request->getModel());
    }

    public function testCouldBeConstructedWithFirstModelAsFirstArgument()
    {
        $request = new ObtainCreditCard($firstModel = new \stdClass());

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertNull($request->getModel());
    }

    public function testCouldBeConstructedWithFirstModelAndCurrentModelAsArguments()
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

        $creditCard = $this->createMock(CreditCardInterface::class);
        $request->set($creditCard);
        $this->assertSame($creditCard, $request->obtain());
    }

    /**
     * @test
     */
    public function shouldAllowObtainPreviouslySetCreditCard()
    {
        $request = new ObtainCreditCard();

        $card = $this->createMock(CreditCardInterface::class);

        $request->set($card);

        $this->assertSame($card, $request->obtain());
    }

    /**
     * @test
     */
    public function throwIfObtainCalledBeforeCreditCardSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Credit card could not be obtained. It has to be set before obtain.');
        $request = new ObtainCreditCard();

        $request->obtain();
    }
}
