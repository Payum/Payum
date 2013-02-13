<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Request\CaptureRequest;
use Payum\PaymentInstructionAggregateInterface;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\AuthorizeNet\Aim\PaymentInstruction;

class CaptureAction extends ActionPaymentAware
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
        if (false == $instruction->getResponseCode()) {
            if ($instruction->getAmount() && $instruction->getCardNum() && $instruction->getExpDate()) {
                $api = clone $this->payment->getApi();

                $instruction->fillRequest($api);
                $instruction->updateFromResponse($api->authorizeAndCapture());
            } else {
                throw new UserInputRequiredInteractiveRequest(array('amount', 'card_num', 'exp_date'));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof CaptureRequest) {
            return false;
        }
        
        return (bool) $this->getPaymentInstructionFromRequest($request);
    }

    /**
     * @param \Payum\Request\CaptureRequest $request
     *
     * @return PaymentInstruction|null
     */
    protected function getPaymentInstructionFromRequest(CaptureRequest $request)
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