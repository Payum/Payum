<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Bridge\Doctrine\Document;

class RecurringPaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInstruction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\RecurringPaymentDetails');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails'));
    }
}