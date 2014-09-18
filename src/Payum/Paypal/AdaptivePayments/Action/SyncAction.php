<?php
namespace Payum\Paypal\AdaptivePayments\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;
use Payum\Paypal\AdaptivePayments\Request\Api\PaymentDetails;

class SyncAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['payKey']) {
            $this->payment->execute(new PaymentDetails($details));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel()
        ;
    }
} 