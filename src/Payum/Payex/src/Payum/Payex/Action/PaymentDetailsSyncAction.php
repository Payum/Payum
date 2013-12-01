<?php
namespace Payum\Payex\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Request\Api\CheckOrderRequest;
use Payum\Request\StatusRequestInterface;
use Payum\Request\SyncRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\CheckAgreementRequest;

class PaymentDetailsSyncAction extends PaymentAwareAction
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
        
        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        if ($model['transactionNumber']) {
            $this->payment->execute(new CheckOrderRequest($request->getModel()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof SyncRequest &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getModel()->offsetExists('transactionNumber')
        ;
    }
}