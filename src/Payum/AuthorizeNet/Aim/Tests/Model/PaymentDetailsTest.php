<?php
namespace Payum\AuthorizeNet\Aim\Tests\Model;

use Payum\AuthorizeNet\Aim\Model\PaymentDetails;

class PaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Model\PaymentDetails');
        
        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Model\PaymentDetails');

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
        $paymentDetails = new PaymentDetails;

        $paymentDetails['response_code'] = 'foo';
        $this->assertEquals('foo', $paymentDetails['response_code']);
        $this->assertEquals('foo', $paymentDetails->getResponseCode());

        $paymentDetails->setAmount('baz');
        $this->assertEquals('baz', $paymentDetails->getAmount());
        $this->assertEquals('baz', $paymentDetails['amount']);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfSetNotSupportedField()
    {
        $paymentDetails = new PaymentDetails;

        $paymentDetails['FOO'] = 'foo';
        
        $this->assertNull($paymentDetails['FOO']);
    }

    /**
     * @test
     */
    public function shouldAllowIterateOverSetFields()
    {
        $paymentDetails = new PaymentDetails;

        $paymentDetails['response_code'] = 'foo';
        $paymentDetails->setAmount('baz');
        
        $this->assertEquals(array('response_code' => 'foo', 'amount' => 'baz'), iterator_to_array($paymentDetails));
    }
}