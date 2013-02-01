<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionInterface;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        // Do nothing
    }
   
    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof InstructionAggregateInterface &&
            $request->getModel()->getInstruction() instanceof PaymentInstruction
        ;
    }
}
