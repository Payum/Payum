<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionPaymentAware;
use Payum\Request\SimpleSellRequest;
use Payum\Request\InstructionAggregateRequestInterface;
use Payum\Request\InstructionAwareRequestInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SaleRequest;

class SimpleSellAction extends ActionPaymentAware
{
    public function execute($request)
    {
        /** @var $request SimpleSellRequest|InstructionAggregateRequestInterface|InstructionAwareRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        if (false == $request->getInstruction()) {
            $request->setInstruction($this->createInstruction());
        }

        $request->getInstruction()->setPaymentrequestNAmt(0, $request->getPrice());
        $request->getInstruction()->setPaymentrequestNCurrencycode(0, $request->getCurrency());

        $this->payment->execute(new SaleRequest($request->getInstruction()));
    }

    public function supports($request)
    {
        return
            $request instanceof SimpleSellRequest &&
            ($request->getInstruction() instanceof Instruction || null === $request->getInstruction()) 
        ;
    }

    /**
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Request\Instruction
     */
    protected function createInstruction()
    {
        return new Instruction();
    }
}