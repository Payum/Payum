<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Request\Api\CheckOrder;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\CheckAgreement;

class PaymentDetailsSyncAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);
        
        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        if ($model['transactionNumber']) {
            $this->payment->execute(new CheckOrder($request->getModel()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getModel()->offsetExists('transactionNumber')
        ;
    }
}