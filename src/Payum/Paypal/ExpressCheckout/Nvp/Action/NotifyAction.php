<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;

class NotifyAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request Notify */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $this->payment->execute(new Sync($request->getModel()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ; 
    }
}
