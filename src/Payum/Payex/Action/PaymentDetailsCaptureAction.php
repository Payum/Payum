<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Request\Api\StartRecurringPaymentRequest;
use Payum\Payex\Request\Api\InitializeOrderRequest;
use Payum\Payex\Request\Api\CompleteOrderRequest;

class PaymentDetailsCaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\Capture */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        if (false == $model['orderRef']) {
            $this->payment->execute(new InitializeOrderRequest($model));
        }

        if ($model['orderRef']) {
            $this->payment->execute(new CompleteOrderRequest($model));
            
            if ($model['recurring']) {
                $this->payment->execute(new StartRecurringPaymentRequest($model));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == (
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        )) {
            return false;
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        if ($model['recurring']) {
            return true;
        }

        //Make sure it is not auto pay payment. There is an other capture action for auto pay payments;
        if (false == $model['autoPay']) {
            return true;
        }
        
        return false;
    }
}