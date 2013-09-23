<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Model;

class BaseModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementArrayAccessInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Model\BaseModel');

        $this->assertTrue($rc->implementsInterface('ArrayAccess'));
    }

    /**
     * @test
     */
    public function shouldImplementIteratorAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Model\BaseModel');

        $this->assertTrue($rc->implementsInterface('IteratorAggregate'));
    }
}