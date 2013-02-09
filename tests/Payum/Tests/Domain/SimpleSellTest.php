<?php
namespace Payum\Tests\Domain;

use Payum\Domain\SimpleSell;

class SimpleSellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentInstructionAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Domain\SimpleSell');
        
        $this->assertTrue($rc->implementsInterface('Payum\PaymentInstructionAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementPaymentInstructionAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Domain\SimpleSell');

        $this->assertTrue($rc->implementsInterface('Payum\PaymentInstructionAggregateInterface'));
    }

    /**
     * @test
     */
    public function couldBeWithoutAnyArguments()
    {
        new SimpleSell();
    }

    /**
     * @test
     */
    public function shouldAllowSetPrice()
    {
        $request = new SimpleSell();
        
        $request->setPrice(100.05);
    }

    /**
     * @test
     */
    public function shouldAllowGetPricePreviouslySet()
    {
        $expectedPrice = 123.45;
        
        $request = new SimpleSell();
        
        $request->setPrice($expectedPrice);
        
        $this->assertEquals($expectedPrice, $request->getPrice());
    }

    /**
     * @test
     */
    public function shouldAllowSetCurrency()
    {
        $request = new SimpleSell();

        $request->setCurrency('USD');
    }

    /**
     * @test
     */
    public function shouldAllowGetCurrencyPreviouslySet()
    {
        $expectedCurrency = 'EUR';

        $request = new SimpleSell();

        $request->setCurrency($expectedCurrency);

        $this->assertEquals($expectedCurrency, $request->getCurrency());
    }

    /**
     * @test
     */
    public function shouldAllowSetPaymentInstruction()
    {
        $request = new SimpleSell();

        $request->setPaymentInstruction(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentInstructionPreviouslySet()
    {
        $expectedInstruction = new \stdClass;

        $request = new SimpleSell();

        $request->setPaymentInstruction($expectedInstruction);

        $this->assertEquals($expectedInstruction, $request->getPaymentInstruction());
    }

    /**
     * @test
     */
    public function shouldGetEmptyStringIfCurrencyIfNotSet()
    {
        $request = new SimpleSell();

        $this->assertSame('', $request->getCurrency());
    }

    /**
     * @test
     */
    public function shouldGetZeroIntIfPriceNotSet()
    {
        $request = new SimpleSell();

        $this->assertSame(0, $request->getPrice());
    }

    /**
     * @test
     */
    public function shouldGetNullIfInstructionNotSet()
    {
        $request = new SimpleSell();

        $this->assertNull($request->getPaymentInstruction());
    }
}