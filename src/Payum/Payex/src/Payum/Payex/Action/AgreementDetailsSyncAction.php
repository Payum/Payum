<?php
namespace Payum\Payex\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Request\SyncRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\CheckAgreementRequest;

class AgreementDetailsSyncAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request SyncRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $this->payment->execute(new CheckAgreementRequest($request->getModel()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof SyncRequest &&
            $request->getModel() instanceof \ArrayAccess &&
            //Make sure it is payment. Apparently an order(payment) does not have this field.
            $request->getModel()->offsetExists('agreementRef') &&
            false == $request->getModel()->offsetExists('orderId')
        ;
    }
}