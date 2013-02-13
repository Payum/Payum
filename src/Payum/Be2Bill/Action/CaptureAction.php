<?php
namespace Payum\Be2Bill\Action;

use Payum\PaymentInstructionAggregateInterface;
use Payum\Request\CaptureRequest;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Be2Bill\PaymentInstruction;
use Payum\Be2Bill\Api;

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
        
        if (null === $instruction->getExeccode()) {
            //instruction must have an alias set (e.g oneclick payment) or credit card info. 
            if ($instruction->getAlias() ||
                ($instruction->getCardcode() && $instruction->getCardcvv() && $instruction->getCardvaliditydate())
            ) {
                $response = $this->payment->getApi()->payment($instruction->toParams());

                $instruction->fromParams((array) $response->getContentJson());
            } else {
                throw new UserInputRequiredInteractiveRequest(array(
                    'cardcode',
                    'cardcvv',
                    'cardvaliditydate',
                    'cardfullname'
                ));
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
