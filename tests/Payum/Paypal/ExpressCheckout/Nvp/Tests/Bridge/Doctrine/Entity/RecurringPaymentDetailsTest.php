<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Bridge\Doctrine\Entity;

class RecurringPaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInstruction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\RecurringPaymentDetails');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails'));
    }
}