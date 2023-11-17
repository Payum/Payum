<?php

namespace Payum\Core\Tests\Request;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\Generic;
use Payum\Core\Request\ObtainCreditCard;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class ObtainCreditCardTest extends TestCase
{
    public function testShouldBeSubClassOfGenericRequest(): void
    {
        $rc = new ReflectionClass(ObtainCreditCard::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testItCouldBeConstructedWithoutAnyArguments(): void
    {
        $request = new ObtainCreditCard();

        $this->assertNull($request->getFirstModel());
        $this->assertNull($request->getModel());
    }

    public function testCouldBeConstructedWithFirstModelAsFirstArgument(): void
    {
        $request = new ObtainCreditCard($firstModel = new stdClass());

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertNull($request->getModel());
    }

    public function testCouldBeConstructedWithFirstModelAndCurrentModelAsArguments(): void
    {
        $request = new ObtainCreditCard($firstModel = new stdClass(), $currentModel = new stdClass());

        $this->assertSame($firstModel, $request->getFirstModel());
        $this->assertSame($currentModel, $request->getModel());
    }

    public function testShouldAllowSetCreditCard(): void
    {
        $request = new ObtainCreditCard();

        $creditCard = $this->createMock(CreditCardInterface::class);
        $request->set($creditCard);
        $this->assertSame($creditCard, $request->obtain());
    }

    public function testShouldAllowObtainPreviouslySetCreditCard(): void
    {
        $request = new ObtainCreditCard();

        $card = $this->createMock(CreditCardInterface::class);

        $request->set($card);

        $this->assertSame($card, $request->obtain());
    }

    public function testThrowIfObtainCalledBeforeCreditCardSet(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Credit card could not be obtained. It has to be set before obtain.');
        $request = new ObtainCreditCard();

        $request->obtain();
    }
}
