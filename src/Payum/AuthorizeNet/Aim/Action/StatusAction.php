<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionInterface;
use Payum\AuthorizeNet\Aim\PaymentInstruction;
use Payum\Domain\InstructionAggregateInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Request\StatusRequestInterface;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel()->getInstruction();
        
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

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof InstructionAggregateInterface &&
            $request->getModel()->getInstruction() instanceof PaymentInstruction
        ;
    }
}