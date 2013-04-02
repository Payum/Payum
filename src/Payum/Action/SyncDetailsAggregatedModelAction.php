<?php
namespace Payum\Action;

use Payum\Exception\RequestNotSupportedException;
use Payum\Model\DetailsAggregateInterface;
use Payum\Request\SyncRequest;

class SyncDetailsAggregatedModelAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request SyncRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $this->payment->execute(
            new SyncRequest($request->getModel()->getDetails())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof SyncRequest &&
            $request->getModel() instanceof DetailsAggregateInterface && 
            $request->getModel()->getDetails()
        ;
    }
}