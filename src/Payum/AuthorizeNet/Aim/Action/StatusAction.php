<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionInterface;
use Payum\AuthorizeNet\Aim\PaymentInstruction;
use Payum\Exception\RequestNotSupportedException;
use Payum\PaymentInstructionAggregateInterface;
use Payum\Request\StatusRequestInterface;
use Payum\Request\SyncRequest;

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