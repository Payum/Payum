<?php
namespace Payum\Be2Bill\Tests\Model;

use Payum\Be2Bill\Model\PaymentDetails;

class PaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Model\PaymentDetails');
        
        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Be2Bill\Model\PaymentDetails');

        $this->assertTrue($rc->implementsInterface('IteratorAggregate'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetails;
    }

    /**
     * @test
     */
    public function shouldAllowUseAsArray()
    {
        $instruction = new PaymentDetails;

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
        $instruction = new PaymentDetails;

        $instruction['FOO'] = 'foo';
    }

    /**
     * @test
     */
    public function shouldAllowIterateOverSetFields()
    {
        $instruction = new PaymentDetails;

        $instruction['EXECCODE'] = 'foo';
        $instruction->setAmount('baz');
        
        $this->assertEquals(array('EXECCODE' => 'foo', 'AMOUNT' => 'baz'), iterator_to_array($instruction));
    }
}