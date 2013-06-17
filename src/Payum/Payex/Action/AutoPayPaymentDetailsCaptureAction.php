<?php
namespace Payum\Payex\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Request\CaptureRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\AutoPayAgreementRequest;

class AutoPayPaymentDetailsCaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $this->payment->execute(new AutoPayAgreementRequest($request->getModel()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess &&
            //Make sure it is auto pay payment.
            $request->getModel()->offsetExists('autoPay') &&
            $request->getModel()->offsetGet('autoPay')
        ;
    }
}