<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Domain\SimpleSell;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp;
use Payum\Request\CreatePaymentInstructionRequest;

class CreateInstructionFromSimpleSellAction extends ActionPaymentAware
{
    /**
     * @var string
     */
    protected $paymentInstructionClass;

    /**
     * @param string $paymentInstructionClass
     */
    public function __construct($paymentInstructionClass = 'Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction')
    {
        $this->paymentInstructionClass = $paymentInstructionClass;
    }
    
    public function execute($request)
    {
        /** @var $request CreatePaymentInstructionRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        /** @var $simpleSell SimpleSell */
        $simpleSell = $request->getModel();
        
        /** @var $instruction \Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction */
        $instruction = new $this->paymentInstructionClass;
        $instruction->setPaymentrequestAmt(0, $simpleSell->getPrice());
        $instruction->setPaymentrequestCurrencycode(0, $simpleSell->getCurrency());
        
        $request->getModel()->setInstruction($instruction);
    }

    public function supports($request)
    {
        return 
            $request instanceof CreatePaymentInstructionRequest &&
            $request->getModel() instanceof SimpleSell
        ;
    }
}