<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\CaptureRequest;
use Payum\Request\SyncRequest;
use Payum\Action\PaymentAwareAction;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckoutRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPaymentRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class CaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        try {
            if (false == $model['PAYMENTREQUEST_0_PAYMENTACTION']) {
                $model['PAYMENTREQUEST_0_PAYMENTACTION'] = Api::PAYMENTACTION_SALE;
            }
            
            if (false == $model['TOKEN']) {
                $this->payment->execute(new SetExpressCheckoutRequest($model));
                $this->payment->execute(new AuthorizeTokenRequest($model));
            }

            $this->payment->execute(new SyncRequest($model));
            
            if (
                $model['PAYERID'] &&  
                Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS'] &&
                $model['PAYMENTREQUEST_0_AMT'] > 0
            ) {
                $this->payment->execute(new DoExpressCheckoutPaymentRequest($model));
            }

            $this->payment->execute(new SyncRequest($model));
        } catch (HttpResponseAckNotSuccessException $e) {
            $model->replace($e->getResponse());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ; 
    }
}
