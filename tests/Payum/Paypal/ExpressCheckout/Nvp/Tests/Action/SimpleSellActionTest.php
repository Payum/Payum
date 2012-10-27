<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Request\SimpleSellRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SimpleSellAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;

class SimpleSellActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfActionPaymentAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\SimpleSellAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionPaymentAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new SimpleSellAction();
    }

    /**
     * @test
     */
    public function shouldSupportSimpleSellRequestAndInstructionNull()
    {
        $action = new SimpleSellAction();

        $request = new SimpleSellRequest();
        //guard
        $this->assertNull($request->getInstruction());
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSimpleSellRequestAndExpressCheckoutInstruction()
    {
        $action = new SimpleSellAction();

        $request = new SimpleSellRequest();
        $request->setInstruction(new Instruction());

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportSimpleSellRequestIfInstructionNotExpressCheckout()
    {
        $anotherInstruction = $this->getMock('Payum\Request\InstructionInterface');
        
        $action = new SimpleSellAction();
        
        $request = new SimpleSellRequest();
        $request->setInstruction($anotherInstruction);

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSimpleSellRequest()
    {
        $action = new SimpleSellAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SimpleSellAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCreateInstructionIfNotSet()
    {
        $action = new SimpleSellAction();
        $action->setPayment($this->createPaymentMock());

        $request = new SimpleSellRequest();
        //guard
        $this->assertNull($request->getInstruction());

        $action->execute($request);

        $this->assertInstanceOf(
            'Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction',
            $request->getInstruction()
        );
    }

    /**
     * @test
     */
    public function shouldSetZeroPaymentCurrencyAndZeroPaymentAmountToInstructionFromSimpleSellRequest()
    {
        $action = new SimpleSellAction();
        $action->setPayment($this->createPaymentMock());

        $request = new SimpleSellRequest(new Instruction);
        $request->setCurrency($expectedCurrency = 'theCurr');
        $request->setPrice($expectedPrice = 'thePrice');
        
        $action->execute($request);
        
        $this->assertEquals($expectedPrice, $request->getInstruction()->getPaymentrequestNAmt(0));
        $this->assertEquals($expectedCurrency, $request->getInstruction()->getPaymentrequestNCurrencycode(0));
    }

    /**
     * @test
     */
    public function shouldRequestSellAction()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->exactly(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\SaleRequest'))
        ;
        
        $action = new SimpleSellAction();
        $action->setPayment($paymentMock);

        $action->execute(new SimpleSellRequest(new Instruction));
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}