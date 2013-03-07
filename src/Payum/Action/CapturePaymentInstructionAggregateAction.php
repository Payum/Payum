<?php
namespace Payum\Action;

use Payum\Exception\RequestNotSupportedException;
use Payum\PaymentInstructionAggregateInterface;
use Payum\Request\CaptureRequest;

class CapturePaymentInstructionAggregateAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $this->payment->execute(
            new CaptureRequest($request->getModel()->getPaymentInstruction())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof PaymentInstructionAggregateInterface && 
            $request->getModel()->getPaymentInstruction()
        ;
    }
}