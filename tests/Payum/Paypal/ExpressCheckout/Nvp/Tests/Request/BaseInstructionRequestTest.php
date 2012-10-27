<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;

class BaseInstructionRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeAbstractClass()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest');
        
        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldImplementInstructionAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest');

        $this->assertTrue($rc->implementsInterface('Payum\Request\InstructionAggregateRequestInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithInstructionAsArgument()
    {
        $this->getMockForAbstractClass(
            'Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest', 
            array(new Instruction)
        );
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionSetInConstructor()
    {
        $expectedInstruction = new Instruction;
        
        $request = $this->getMockForAbstractClass(
            'Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest',
            array($expectedInstruction)
        );
        
        $this->assertSame($expectedInstruction, $request->getInstruction());
    }
}
