<?php
namespace Payum\Be2Bill\Action;

use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;
use Payum\PaymentInstructionAggregateInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Be2Bill\PaymentInstruction;
use Payum\Be2Bill\Api;

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
        $instruction = $request->getModel()->getPaymentInstruction();
        if (null === $instruction->getExeccode()) {
            $request->markNew();
            
            return;
        }
        if (Api::EXECCODE_SUCCESSFUL === $instruction->getExeccode()) {
            $request->markSuccess();

            return;
        }

        $request->markFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof PaymentInstruction
        ;
    }
}