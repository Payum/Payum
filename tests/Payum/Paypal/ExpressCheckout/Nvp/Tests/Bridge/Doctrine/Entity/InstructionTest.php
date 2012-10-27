<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Bridge\Doctrine\Entity;

class InstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInstruction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\Instruction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction'));
    }
}