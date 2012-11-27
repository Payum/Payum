<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\InstructionAggregateRequestInterface;
use Payum\Request\StatusRequestInterface;
use Payum\AuthorizeNet\Aim\Request\Instruction;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        /** @var $internalRequest InstructionAggregateRequestInterface */
        $internalRequest = $request->getRequest();
        
        /** @var $instruction Instruction */
        $instruction = $internalRequest->getInstruction();
        
        if (\AuthorizeNetAIM_Response::APPROVED == $instruction->getResponseCode()) {
            $request->markSuccess();
            
            return;
        }

        if (\AuthorizeNetAIM_Response::DECLINED == $instruction->getResponseCode()) {
            $request->markCanceled();

            return;
        }

        if (\AuthorizeNetAIM_Response::ERROR == $instruction->getResponseCode()) {
            $request->markFailed();

            return;
        }

        if (\AuthorizeNetAIM_Response::HELD == $instruction->getResponseCode()) {
            $request->markInProgress();

            return;
        }
    }
   
    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            $request->getRequest() instanceof InstructionAggregateRequestInterface &&
            $request->getRequest()->getInstruction() instanceof Instruction
        ;
    }
}