<?php
namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\PaymentInstruction;

class PaymentInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\PaymentInstruction');
        
        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\PaymentInstruction');

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

        $instruction['EXECCODE'] = 'foo';
        $this->assertEquals('foo', $instruction['EXECCODE']);
        $this->assertEquals('foo', $instruction->getExeccode());

        $instruction->setAmount('baz');
        $this->assertEquals('baz', $instruction->getAmount());
        $this->assertEquals('baz', $instruction['AMOUNT']);
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

        $instruction['EXECCODE'] = 'foo';
        $instruction->setAmount('baz');
        
        $this->assertEquals(array('EXECCODE' => 'foo', 'AMOUNT' => 'baz'), iterator_to_array($instruction));
    }
}