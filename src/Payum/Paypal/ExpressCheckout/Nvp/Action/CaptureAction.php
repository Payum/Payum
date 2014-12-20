<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class CaptureAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['PAYMENTREQUEST_0_PAYMENTACTION']) {
            $model['PAYMENTREQUEST_0_PAYMENTACTION'] = Api::PAYMENTACTION_SALE;
        }

        if (false == $model['TOKEN']) {
            if (false == $model['RETURNURL'] && $request->getToken()) {
                $model['RETURNURL'] = $request->getToken()->getTargetUrl();
            }

            if (false == $model['CANCELURL'] && $request->getToken()) {
                $model['CANCELURL'] = $request->getToken()->getTargetUrl();
            }

            $this->payment->execute(new SetExpressCheckout($model));

            if ($model['L_ERRORCODE0']) {
                return;
            }

            $this->payment->execute(new AuthorizeToken($model));
        }

        $this->payment->execute(new Sync($model));

        if (
            $model['PAYERID'] &&
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $model['CHECKOUTSTATUS'] &&
            $model['PAYMENTREQUEST_0_AMT'] > 0
        ) {
            $this->payment->execute(new DoExpressCheckoutPayment($model));
        }

        $this->payment->execute(new Sync($model));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
