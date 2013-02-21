<?php
namespace Payum\AuthorizeNet\Aim\Tests;

use Payum\AuthorizeNet\Aim\PaymentInstruction;

class PaymentInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\PaymentInstruction');
        
        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\PaymentInstruction');

        $this->assertTrue($rc->implementsInterface('IteratorAggregate'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentInstruction;
    }

    /**
     * @test
     */
    public function shouldAllowUseAsArray()
    {
        $instruction = new PaymentInstruction;

        $instruction['response_code'] = 'foo';
        $this->assertEquals('foo', $instruction['response_code']);
        $this->assertEquals('foo', $instruction->getResponseCode());

        $instruction->setAmount('baz');
        $this->assertEquals('baz', $instruction->getAmount());
        $this->assertEquals('baz', $instruction['amount']);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unsupported offset given FOO.
     */
    public function throwIfSetNotBe2billArrayField()
    {
        $instruction = new PaymentInstruction;

        $instruction['FOO'] = 'foo';
    }

    /**
     * @test
     */
    public function shouldAllowIterateOverSetFields()
    {
        $instruction = new PaymentInstruction;

        $instruction['response_code'] = 'foo';
        $instruction->setAmount('baz');
        
        $this->assertEquals(array('response_code' => 'foo', 'amount' => 'baz'), iterator_to_array($instruction));
    }
}