<?php
namespace Payum\Payex\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Payex\Api\OrderApi;
use Payum\Request\CaptureRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\InitializeOrderRequest;
use Payum\Payex\Request\Api\CompleteOrderRequest;

class PaymentDetailsCaptureAction extends PaymentAwareAction
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

        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        if (false == $model['orderRef']) {
            $this->payment->execute(new InitializeOrderRequest($model));
        }

        if ($model['orderRef']) {
            $this->payment->execute(new CompleteOrderRequest($model));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest && 
            $request->getModel() instanceof \ArrayAccess &&
            //Make sure it is not auto pay payment. There is an other capture action for auto pay payments;
            false == (
                $request->getModel()->offsetExists('autoPay') && 
                $request->getModel()->offsetGet('autoPay')
            )
        ;
    }
}