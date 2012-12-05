<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Bridge\Doctrine\Entity;

class PaymentInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInstruction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\PaymentInstruction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction'));
    }
}