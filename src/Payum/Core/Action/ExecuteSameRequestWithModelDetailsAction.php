<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Request\ModelAwareInterface;

class ExecuteSameRequestWithModelDetailsAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request ModelAwareInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setModel($request->getModel()->getDetails());
        
        $this->payment->execute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof ModelAwareInterface &&
            $request->getModel() instanceof DetailsAggregateInterface && 
            $request->getModel()->getDetails()
        ;
    }
}