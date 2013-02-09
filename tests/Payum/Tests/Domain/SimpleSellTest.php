<?php
namespace Payum\Tests\Domain;

use Payum\Domain\SimpleSell;

class SimpleSellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInstructionAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Domain\SimpleSell');
        
        $this->assertTrue($rc->implementsInterface('Payum\Domain\InstructionAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementInstructionAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Domain\SimpleSell');

        $this->assertTrue($rc->implementsInterface('Payum\Domain\InstructionAggregateInterface'));
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
    public function shouldAllowSetInstruction()
    {
        $request = new SimpleSell();

        $request->setInstruction(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionPreviouslySet()
    {
        $expectedInstruction = new \stdClass;

        $request = new SimpleSell();

        $request->setInstruction($expectedInstruction);

        $this->assertEquals($expectedInstruction, $request->getInstruction());
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

        $this->assertNull($request->getInstruction());
    }
}