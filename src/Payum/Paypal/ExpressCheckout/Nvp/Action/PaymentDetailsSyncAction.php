<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest;

class PaymentDetailsSyncAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['TOKEN']) {
            return;
        }
        
        $this->payment->execute(new GetExpressCheckoutDetailsRequest($model));

        foreach (range(0, 9) as $index) {
            if ($model['PAYMENTREQUEST_'.$index.'_TRANSACTIONID']) {
                $this->payment->execute(new GetTransactionDetailsRequest($model, $index));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof Sync) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }

        return isset($model['PAYMENTREQUEST_0_AMT']) && null !== $model['PAYMENTREQUEST_0_AMT'];
    }
}