<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest;

class AuthorizeTokenRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseInstructionRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithInstructionAsArgument()
    {
        $request = new AuthorizeTokenRequest(new Instruction);
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionSetInConstructor()
    {
        $expectedInstruction = new Instruction;

        $request = new AuthorizeTokenRequest($expectedInstruction);
        
        $this->assertSame($expectedInstruction, $request->getInstruction());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultForceSetToFalseByDefault()
    {
        $request = new AuthorizeTokenRequest(new Instruction);
        
        $this->assertFalse($request->isForced());
    }

    /**
     * @test
     */
    public function shouldAllowGetForceSetInConstructor()
    {
        $request = new AuthorizeTokenRequest(new Instruction, $force = true);

        $this->assertTrue($request->isForced());
    }
}
