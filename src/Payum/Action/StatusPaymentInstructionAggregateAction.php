<?php
namespace Payum\Action;

use Payum\Exception\RequestNotSupportedException;
use Payum\PaymentInstructionAggregateInterface;
use Payum\Request\StatusRequestInterface;

class StatusPaymentInstructionAggregateAction extends ActionPaymentAware
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
        
        $model = $request->getModel();
        
        $request->setModel($model->getPaymentInstruction());
        $this->payment->execute($request);

        $request->setModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof PaymentInstructionAggregateInterface && 
            $request->getModel()->getPaymentInstruction()
        ;
    }
}