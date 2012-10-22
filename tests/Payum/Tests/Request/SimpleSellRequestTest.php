<?php
namespace Payum\Tests\Request;

use Payum\Request\SimpleSellRequest;

class SimpleSellRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementInstructionAwareRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\SimpleSellRequest');
        
        $this->assertTrue($rc->implementsInterface('Payum\Request\InstructionAwareRequestInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementInstructionAggregateRequestInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\SimpleSellRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Request\InstructionAggregateRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeWithoutAnyArguments()
    {
        new SimpleSellRequest();
    }

    /**
     * @test
     */
    public function shouldAllowSetPrice()
    {
        $request = new SimpleSellRequest();
        
        $request->setPrice(100.05);
    }

    /**
     * @test
     */
    public function shouldAllowGetPricePreviouslySet()
    {
        $expectedPrice = 123.45;
        
        $request = new SimpleSellRequest();
        
        $request->setPrice($expectedPrice);
        
        $this->assertEquals($expectedPrice, $request->getPrice());
    }

    /**
     * @test
     */
    public function shouldAllowSetCurrency()
    {
        $request = new SimpleSellRequest();

        $request->setCurrency('USD');
    }

    /**
     * @test
     */
    public function shouldAllowGetCurrencyPreviouslySet()
    {
        $expectedCurrency = 'EUR';

        $request = new SimpleSellRequest();

        $request->setCurrency($expectedCurrency);

        $this->assertEquals($expectedCurrency, $request->getCurrency());
    }

    /**
     * @test
     */
    public function shouldAllowSetInstruction()
    {
        $request = new SimpleSellRequest();

        $request->setInstruction($this->createInstructionMock());
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionPreviouslySet()
    {
        $expectedInstruction = $this->createInstructionMock();

        $request = new SimpleSellRequest();

        $request->setInstruction($expectedInstruction);

        $this->assertEquals($expectedInstruction, $request->getInstruction());
    }

    /**
     * @test
     */
    public function shouldGetEmptyStringIfCurrencyIfNotSet()
    {
        $request = new SimpleSellRequest();

        $this->assertSame('', $request->getCurrency());
    }

    /**
     * @test
     */
    public function shouldGetZeroIntIfPriceNotSet()
    {
        $request = new SimpleSellRequest();

        $this->assertSame(0, $request->getPrice());
    }

    /**
     * @test
     */
    public function shouldGetNullIfInstructionNotSet()
    {
        $request = new SimpleSellRequest();

        $this->assertNull($request->getInstruction());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Request\InstructionInterface
     */
    protected function createInstructionMock()
    {
        return $this->getMock('Payum\Request\InstructionInterface');
    }
}