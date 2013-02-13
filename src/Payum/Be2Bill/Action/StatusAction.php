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
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $this->getPaymentInstructionFromRequest($request);
        
        if (null === $instruction->getExeccode()) {
            $request->markNew();
            
            return;
        }
        if (Api::EXECCODE_SUCCESSFUL === $instruction->getExeccode()) {
            $request->markSuccess();

            return;
        }
        
        //TODO add more checks.

        $request->markFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof StatusRequestInterface) {
            return false;
        }
        
        return (bool) $this->getPaymentInstructionFromRequest($request);
    }

    /**
     * @param \Payum\Request\CaptureRequest $request
     *
     * @return PaymentInstruction|null
     */
    protected function getPaymentInstructionFromRequest(StatusRequestInterface $request)
    {
        if ($request->getModel() instanceof PaymentInstruction) {
            return $request->getModel();
        }

        if (
            $request->getModel() instanceof PaymentInstructionAggregateInterface &&
            $request->getModel()->getPaymentInstruction() instanceof PaymentInstruction
        ) {
            return $request->getModel()->getPaymentInstruction();
        }
    }
}