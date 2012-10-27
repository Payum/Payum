<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;

class GetExpressCheckoutDetailsRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseInstructionRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithInstructionAsArgument()
    {
        new GetExpressCheckoutDetailsRequest(new Instruction);
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionSetInConstructor()
    {
        $expectedInstruction = new Instruction;

        $request = new GetExpressCheckoutDetailsRequest($expectedInstruction);
        
        $this->assertSame($expectedInstruction, $request->getInstruction());
    }
}
