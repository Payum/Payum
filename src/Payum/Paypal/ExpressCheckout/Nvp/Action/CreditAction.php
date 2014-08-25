<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Credit;
use Payum\Core\Request\SecuredCredit;
use Payum\Core\Request\Sync;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\APApi;

class CreditAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request Credit */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['returnUrl'] && $request instanceof SecuredCredit) {
            $model['returnUrl'] = $request->getToken()->getTargetUrl();
        }

        if (false == $model['cancelUrl'] && $request instanceof SecuredCredit) {
            $model['cancelUrl'] = $request->getToken()->getTargetUrl();
        }

        $this->payment->execute(new setSimpleAdaptivePayment($model));

        $this->payment->execute(new Sync($model));

        /*
         * TODO: Check for something before calling 'pay'?
         */
        $this->payment->execute(new pay($model));

        $this->payment->execute(new Sync($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof Credit &&
            $request->getModel() instanceof \ArrayAccess
        ; 
    }
}
