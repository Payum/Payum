<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\SyncRequest;
use Payum\Action\PaymentAwareAction;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetailsRequest;

class PaymentDetailsSyncAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request SyncRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['TOKEN']) {
            return;
        }
        
        try {
            $this->payment->execute(new GetExpressCheckoutDetailsRequest($model));
            
            foreach (range(0, 9) as $index) {
                if ($model['PAYMENTREQUEST_'.$index.'_TRANSACTIONID']) {
                    $this->payment->execute(new GetTransactionDetailsRequest($model, $index));
                }
            }
        } catch (HttpResponseAckNotSuccessException $e) {
            $model->replace($e->getResponse());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof SyncRequest) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }

        return isset($model['PAYMENTREQUEST_0_AMT']) && null !== $model['PAYMENTREQUEST_0_AMT'];
    }
}