<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionPaymentAware;
use Payum\Request\SimpleSellRequest;
use Payum\Request\InstructionAggregateRequestInterface;
use Payum\Request\InstructionAwareRequestInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\AuthorizeNet\Aim\Request\Instruction;
use Payum\AuthorizeNet\Aim\Request\AuthorizeAndCaptureRequest;;

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
        
        /** @var $instruction \Payum\AuthorizeNet\Aim\Request\Instruction */
        $instruction = $request->getInstruction(); 

        $instruction->setAmount($request->getPrice());

        $this->payment->execute(new AuthorizeAndCaptureRequest($request->getInstruction()));
    }

    public function supports($request)
    {
        return
            $request instanceof SimpleSellRequest &&
            ($request->getInstruction() instanceof Instruction || null === $request->getInstruction())
        ;
    }

    /**
     * @return \Payum\AuthorizeNet\Aim\Request\Instruction
     */
    protected function createInstruction()
    {
        return new Instruction();
    }
}