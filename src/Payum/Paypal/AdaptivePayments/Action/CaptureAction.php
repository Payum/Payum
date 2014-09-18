<?php
namespace Payum\Paypal\AdaptivePayments\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Paypal\AdaptivePayments\Request\Api\Pay;

class CaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['payKey']) {
            $this->payment->execute(new Pay($details));
        }

        $this->payment->execute(new Sync($details));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel()
        ;
    }
}