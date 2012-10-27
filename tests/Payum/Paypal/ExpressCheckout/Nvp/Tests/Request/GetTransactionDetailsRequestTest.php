<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest;

class GetTransactionDetailsRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseInstructionRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\GetTransactionDetailsRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Request\BaseInstructionRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithPaymentRequestNAndInstructionAsArguments()
    {
        new GetTransactionDetailsRequest($paymentRequestN = 5, new Instruction);
    }

    /**
     * @test
     */
    public function shouldAllowGetInstructionSetInConstructor()
    {
        $expectedInstruction = new Instruction;

        $request = new GetTransactionDetailsRequest($paymentRequestN = 5, $expectedInstruction);
        
        $this->assertSame($expectedInstruction, $request->getInstruction());
    }

    /**
     * @test
     */
    public function shouldAllowGetPaymentRequestBInstructionSetInConstructor()
    {
        $expectedPaymentRequestN = 7;

        $request = new GetTransactionDetailsRequest($expectedPaymentRequestN, new Instruction);

        $this->assertSame($expectedPaymentRequestN, $request->getPaymentRequestN());
    }
}
