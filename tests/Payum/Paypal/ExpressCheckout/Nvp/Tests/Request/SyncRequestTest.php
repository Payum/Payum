<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest;

class SyncRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseInstructionRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithInstructionAsArgument()
    {
        new SyncRequest(new Instruction);
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionSetInConstructor()
    {
        $expectedInstruction = new Instruction;

        $request = new SyncRequest($expectedInstruction);
        
        $this->assertSame($expectedInstruction, $request->getInstruction());
    }
}
