<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Request\NotifyRequest;
use Payum\Core\Request\SyncRequest;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;

class NotifyAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request NotifyRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $this->payment->execute(new SyncRequest($request->getModel()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof NotifyRequest &&
            $request->getModel() instanceof \ArrayAccess
        ; 
    }
}
