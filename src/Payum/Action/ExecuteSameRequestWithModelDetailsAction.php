<?php
namespace Payum\Action;

use Payum\Exception\RequestNotSupportedException;
use Payum\Model\DetailsAggregateInterface;
use Payum\Request\ModelRequestInterface;

class ExecuteSameRequestWithModelDetailsAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request ModelRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $request->setModel($request->getModel()->getDetails());
        
        $this->payment->execute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof ModelRequestInterface &&
            $request->getModel() instanceof DetailsAggregateInterface && 
            $request->getModel()->getDetails()
        ;
    }
}