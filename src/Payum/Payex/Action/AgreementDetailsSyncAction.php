<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\CheckAgreement;

class AgreementDetailsSyncAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $this->payment->execute(new CheckAgreement($request->getModel()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess &&
            //Make sure it is payment. Apparently an order(payment) does not have this field.
            $request->getModel()->offsetExists('agreementRef') &&
            false == $request->getModel()->offsetExists('orderId')
        ;
    }
}
